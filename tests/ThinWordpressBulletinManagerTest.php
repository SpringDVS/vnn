<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\ThinWordpressBulletinManager;
use App\Models\ThinServices;
use SpringDvs\Core\NetServices\BulletinHeader;
use SpringDvs\Core\LocalNodeInterface;

class ThinWordpressBulletinManagerTest extends TestCase
{
	/**
	 * @var ThinWordpressBulletinManager
	 */
	private $service;
	
	/**
	 * @var ThinServices
	 */
	private $thinService;
	
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
			
		DB::table('userassoc')->insert(
				['uid' => 1, 'nodeid' => $this->nodeId]
				);
		
		
		$this->thinService = $this->app->make(ThinServices::class);
		
		$_SERVER['HTTP_HOST'] = 'section9';
	}

    public function testEmtpyFilterSuccess()
    {
    	$this->configThinService(); $this->setupServices();
    	
    	$headers = $this->service->withFilters();
    	
    	$this->assertCount(3, $headers);
    	$titles = ['Generic Title','Services Title', 'Events Title'];
    	
    	foreach($headers as $index => $header) {
    		$this->assertEquals($titles[$index], $header->title());
    	}
    }
    
    public function testCategoryEventsFilterSuccess()
    {
    	$this->configThinService(); $this->setupServices();
    	 
    	$headers = $this->service->withFilters(['categories' => 'Events']);
    	 
    	$this->assertCount(1, $headers);
    	$titles = ['Events Title'];
    	 
    	foreach($headers as $index => $header) {
    		$this->assertEquals($titles[$index], $header->title());
    	}
    }
    
    public function testCategoryServicesFilterSuccess()
    {
    	$this->configThinService(); $this->setupServices();
    
    	$headers = $this->service->withFilters(['categories' => 'Services']);
    
    	$this->assertCount(1, $headers);
    	$titles = ['Services Title'];
    
    	foreach($headers as $index => $header) {
    		$this->assertEquals($titles[$index], $header->title());
    	}
    }
    
    public function testEmptyCategoryTagKeysSuccess()
    {
    	$this->configThinService(); $this->setupServices();
    	 
    	$headers = $this->service->withFilters(['tags' => 'keys']);
    	 
    	$this->assertCount(2, $headers);
    	$titles = ['Generic Title','Events Title'];
    	 
    	foreach($headers as $index => $header) {
    		$this->assertEquals($titles[$index], $header->title());
    	}
    }
    
    public function testEventsCategoryTagKeysSuccess()
    {
    	$this->configThinService(); $this->setupServices();
    
    	$headers = $this->service->withFilters(['categories' => 'Events','tags' => 'keys']);
    
    	$this->assertCount(1, $headers);
    	$titles = ['Events Title'];
    
    	foreach($headers as $index => $header) {
    		$this->assertEquals($titles[$index], $header->title());
    	}
    }
    
    public function testEventsCategoryTagInvalidFailure()
    {
    	$this->configThinService(); $this->setupServices();
    
    	$headers = $this->service->withFilters(['categories' => 'Events','tags' => 'invalid']);
    
    	$this->assertCount(0, $headers);
    }
    
    public function testEmtpyFilterLimitOneSuccess()
    {
    	$this->configThinService(); $this->setupServices();
    	 
    	$headers = $this->service->withFilters(['limit' => 1]);
    	 
    	$this->assertCount(1, $headers);
    	$titles = ['Generic Title'];
    	 
    	foreach($headers as $index => $header) {
    		$this->assertEquals($titles[$index], $header->title());
    	}
    }
    
    public function testGetUid()
    {
    	$this->configThinService(); $this->setupServices();
    
    	$headers = $this->service->withFilters(['limit' => 1]);
    
    	$this->assertCount(1, $headers);
    	$titles = ['Generic Title'];
    
    	foreach($headers as $index => $header) {
    		$this->assertEquals($titles[$index], $header->title());
    	}
    	
    	$bulletin = $this->service->withUid($headers[0]->uid());
    	$this->assertEquals('http://mainline.wp/2017/01/24/this-is-a-generic-springnet-post/', $bulletin->content());
    }

    public function testGetUidFailure()
    {
    	$this->configThinService(); $this->setupServices();
    
    	$bulletin = $this->service->withUid('http://mainline.wp/?p=10004');
    	$this->assertNull($bulletin);
    }
    private function configThinService() {
    	$this->thinService->setService('bulletin', 'wordpress', $this->nodeId);
    }
    private function setupServices() {
    	$this->thinService->setConfig('bulletin', $this->nodeId, (object)[
    			'feedUri' => 'http://mainline.wp', 'categoryBase' => 'SpringNet'
    	]);
    	$this->service = $this->app->make(ThinWordpressBulletinManager::class,
    									['localNode' => $this->app->make(LocalNodeInterface::class)]);
    }
}
