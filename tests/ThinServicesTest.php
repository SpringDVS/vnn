<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\ThinServices;

class ThinServicesTest extends TestCase
{
	/**
	 * @var ThinServices The services lookup
	 */
	private $thinServices;
	
	public function setUp() {
		parent::setUp();
		$this->thinServices = $this->app->make(ThinServices::class);
	}

    public function testSetThinServices()
    {
		$this->thinServices->setService('bulletin', 'wordpress', 1);
		$actual = DB::table('thin_services')
			->select(['nodeid','type','module'])
			->where('nodeid', '=', 1)
			->first();
	
		$this->assertNotNull($actual);
		$this->assertEquals(1, $actual->nodeid);
		$this->assertEquals('bulletin', $actual->module);
		$this->assertEquals('wordpress', $actual->type);
    }
    
    public function testUpdateThinServices()
    {
    	$this->thinServices->setService('bulletin', 'wordpress', 1);
    	$this->thinServices->setService('bulletin', 'other', 1);
    	$actual = DB::table('thin_services')
    		->select(['nodeid','type','module'])
    		->where('nodeid', '=', 1)
    		->first();
    
    	$this->assertNotNull($actual);
    	$this->assertEquals(1, $actual->nodeid);
    	$this->assertEquals('bulletin', $actual->module);
    	$this->assertEquals('other', $actual->type);
    }
    
    public function testRemoveThinServices()
    {
    	$this->thinServices->setService('bulletin', 'wordpress', 1);
    	$this->thinServices->removeService('bulletin', 1);
    	$actual = DB::table('thin_services')
	    	->select(['nodeid','type','module'])
	    	->where('nodeid', '=', 1)
	    	->first();
    
    	$this->assertNull($actual);
    }
    
    public function testGetServiceSuccess() {
    	$this->thinServices->setService('bulletin', 'wordpress', 1);
    	$this->assertEquals('wordpress', $this->thinServices->service('bulletin', 1));
    }
    
    public function testGetServiceFailure() {
    	$this->thinServices->setService('bulletin', 'wordpress', 1);
    	$this->assertNull($this->thinServices->service('bulletin', 2));
    }
    
    public function testConfig() {
    	$this->thinServices->setService('bulletin', 'wordpress', 1);
    	
    	$this->thinServices->setConfig('bulletin', 1, (object)[
    			'feedUri' => 'foo', 'categoryBase' => 'bar'
    	]);
    	
    	$config = $this->thinServices->getConfig('bulletin', 1);
    	
    	$this->assertEquals('foo', $config->feedUri);
    	$this->assertEquals('bar', $config->categoryBase);
    	
    }
}
