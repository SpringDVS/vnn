<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;


class LocalNodeModelTest extends TestCase
{
	/**
	 * @var \SpringDvs\Core\LocalNodeInterface The local node interface
	 */
	private $node;
	
	
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
    	
    	$this->node = $this->app->make('SpringDvs\Core\LocalNodeInterface');
    	
    	$_SERVER['HTTP_HOST'] = 'section9';
    }

    public function testSpringnameRetrieval()
    {
    	$this->assertEquals('alpha', $this->node->springname());
    }
    
    public function testRegionalRetrieval()
    {
    	$this->assertEquals('venus', $this->node->regional());
    }
    
    public function testTopRetrieval()
    {
    	$this->assertEquals('uk', $this->node->top());
    }

    public function testUriRetrieval()
    {
    	$this->assertEquals('alpha.venus.uk', $this->node->uri());
    }
    
    public function testHostnameRetrieval()
    {
    	$this->assertEquals($_SERVER['HTTP_HOST'], $this->node->hostname());
    }
    
    public function testHostpathRetrieval()
    {
    	$this->assertEquals('/virt/venus/alpha', $this->node->hostpath());
    }
    
    public function testHostfieldRetrieval()
    {
    	$expected = $_SERVER['HTTP_HOST'].'/virt/venus/alpha';
    	$this->assertEquals($expected, $this->node->hostfield());
    }
}
