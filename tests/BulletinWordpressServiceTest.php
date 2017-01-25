<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use SpringDvs\Core\NetServiceHandler;
use SpringDvs\Core\LocalNodeInterface;
use SpringDvs\Uri;
use App\Http\Controllers\SpringNodeController;
use Illuminate\Http\Request;
use App\Models\ThinServices;

class BulletinWordpressServiceTest extends TestCase
{
	/**
	 * @var SpringNodeController;
	 */
	private $controller;
	
	/**
	 * @var Request
	 */
	private $request;
	
	public function setUp() {
		parent::setUp();
		$netId = DB::table('regionals')->insert(
				['network' => 'venus']
				);
			
		$this->nodeId = DB::table('nodes')->insertGetId(
				['springname' => 'alpha']
				);
			
		DB::table('clusters')->insert(
				['netid' => $netId, 'nodeid' => $this->nodeId]
				);
	
		$this->controller = $this->app->make(SpringNodeController::class);
		
		$_SERVER['HTTP_HOST'] = 'section9';
	}

    public function testRootResource()
    {
    	$this->configThinService($this->serviceConfig());
    	
    	$this->mockRequst('service spring://alpha.venus.uk/bulletin/');
    	
    	$response = $this->controller->spring('venus', 'alpha', $this->request);

    	$headers= MessageDecoder::jsonServiceTextStripNode($response);

    	$this->assertCount(3, $headers);
    	$titles = ['Generic Title','Services Title', 'Events Title'];

    	foreach($headers as $index => $header) {
    		$this->assertEquals($titles[$index], $header['title'][0]);
    		$this->assertEquals('web', $header['source']);
    	}
    }
    
    public function testPostStrippedView()
    {
    	$this->configThinService($this->serviceConfig());
    	$uid = base64_encode("http://mainline.wp/?p=6");
    	
    	$this->mockRequst("service spring://alpha.venus.uk/bulletin/post/$uid?view=test response:stripped");
    	 
    	$response = $this->controller->spring('venus', 'alpha', $this->request);
    
    	$this->assertEquals('http://mainline.wp/2017/01/24/this-is-an-event-post-for-springnet/', $response);
    }

    private function configThinService($config = null) {
    	/**
    	 * @var ThinServices $services
    	 */
    	$services = $this->app->make(ThinServices::class);
    	$services->setService('bulletin', 'wordpress', $this->nodeId);
    	if($config){
    			$services->setConfig('bulletin', $this->nodeId, (object)$config);
    	}
    }
    
    private function serviceConfig() {
    	return (object)[
    			'feedUri' => 'http://mainline.wp',
    			'categoryBase' => 'SpringNet'
    		];
    }

    private function mockRequst($uri) {
    	$rq = $this->getMockBuilder(Request::class)
    		->setMethods(['getContent'])
    		->getMock();
    	
    	$rq->expects($this->any())
    		->method('getContent')
    		->withAnyParameters()
    		->willReturn($uri);
    	
    	$this->request = $rq;
    }
}
