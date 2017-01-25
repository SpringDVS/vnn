<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Http\Controllers\SpringNodeController;
use SpringDvs\Core\NetServices\OrgProfileManagerInterface;
use SpringDvs\Core\LocalNodeInterface;
use SpringDvs\Core\NetServices\OrgProfile;
use App\Models\LocalNodeModel;
use App\Models\VirtualNodeModel;
use Illuminate\Http\Request;

class OrgProfileServiceTest extends TestCase
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
		
		/**
		 * @var OrgProfileManagerInterface $profile
		 */
		$profile = $this->app->make(OrgProfileManagerInterface::class,
									['localNode' => new VirtualNodeModel(1, 'alpha', '', '', 'venus', 'uk')]);
		
		$profile->updateProfile(new OrgProfile('foo', 'bar', ['tag1','tag2']));
		$_SERVER['HTTP_HOST'] = 'section9';
	}

    public function testRootResource()
    {
		$this->mockRequst("service spring://alpha.venus.uk/orgprofile");
		$response = $this->controller->spring('venus', 'alpha', $this->request);
		
		$profile = MessageDecoder::jsonServiceTextStripNode($response);
		$this->assertNotNull($profile);
		$this->assertEquals('foo', $profile['name']);
		$this->assertEquals('bar', $profile['website']);
		$this->assertEquals(['tag1','tag2'], $profile['tags']);
		
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
