<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\VnnServerModel;
use App\Models\LocalNodeLookup;

class LocalNodeLookupTest extends TestCase
{
	/**
	 * @var \App\Models\LocalNodeLookup Lookup object for nodes
	 */
	private $lookup;
	
	public function setUp() {
		parent::setUp();
		$netId = DB::table('regionals')->insert(
				['network' => 'venus']
				);
			
		$this->nodeId = DB::table('nodes')->insert(
				['springname' => 'alpha']
				);
			
		DB::table('clusters')->insert(
				['netid' => $netId, 'nodeid' => $this->nodeId]
				);
			
		DB::table('userassoc')->insert(
				['uid' => 1, 'nodeid' => $this->nodeId]
				);
			
		$this->repo = $this->app->make('SpringDvs\Core\NetServices\BulletinManagerInterface');
			
		$_SERVER['HTTP_HOST'] = 'section9';
		$this->lookup = $this->app->make(LocalNodeLookup::class);
	}

    public function testLookupSuccess()
    {
		$local = $this->lookup->fromSpring('alpha', 'venus');
		
		$this->assertNotNull($local);
		$this->assertEquals('alpha', $local->springname());
		$this->assertEquals('venus', $local->regional());
		$this->assertEquals('uk', $local->top());
		$this->assertEquals('virt/venus/alpha', $local->hostpath());
		$this->assertEquals('section9', $local->hostname());
		$this->assertEquals('section9/virt/venus/alpha', $local->hostfield());
		$this->assertEquals($this->nodeId, $local->nodeid());
    }
    
    public function testLookupFailure()
    {
    	$local = $this->lookup->fromSpring('invalid', 'venus');
    	
    	$this->assertNull($local);
    }
}
