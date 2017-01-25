<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use SpringDvs\Core\NetServices\OrgProfileManagerInterface;
use SpringDvs\Core\NetServices\OrgProfile;

class OrgProfileManagerTest extends TestCase
{
	/**
	 * @var \SpringDvs\Core\LocalNodeInterface The local node interface
	 */
	private $node;
	
	/**
	 * @var \SpringDvs\Core\NetServices\OrgProfileManagerInterface
	 */
	private $manager;

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
		 
		$this->node = $this->app->make(SpringDvs\Core\LocalNodeInterface::class);
		$this->manager = $this->app->make(OrgProfileManagerInterface::class,['localNode' => $this->node]);
		$_SERVER['HTTP_HOST'] = 'section9';
	}

	public function testSetProfileWithWebsiteTags()
    {
		
        $this->manager->updateProfile(new OrgProfile('foo', 'bar', ['tag1','tag2']));
        
        $actual = DB::table('orgprofiles')
        	->select(['name','website','tags'])
        	->where('nodeid', '=', $this->node->nodeid())
        	->first();
        
        $this->assertNotNull($actual);
        $this->assertEquals('foo', $actual->name);
        $this->assertEquals('bar', $actual->website);
        $this->assertEquals('tag1,tag2', $actual->tags);
    }
    
    public function testSetProfileWithWebsiteWithoutTags()
    {
    
    	$this->manager->updateProfile(new OrgProfile('foo', 'bar', []));
    
    	$actual = DB::table('orgprofiles')
    	->select(['name','website','tags'])
    	->where('nodeid', '=', $this->node->nodeid())
    	->first();
    
    	$this->assertNotNull($actual);
    	$this->assertEquals('foo', $actual->name);
    	$this->assertEquals('bar', $actual->website);
    	$this->assertEquals('', $actual->tags);
    }

    public function testSetProfileWithTagsWithoutWebsite()
    {
    
    	$this->manager->updateProfile(new OrgProfile('foo', '', ['tag1','tag2']));
    
    	$actual = DB::table('orgprofiles')
    		->select(['name','website','tags'])
    		->where('nodeid', '=', $this->node->nodeid())
    		->first();
    
    	$this->assertNotNull($actual);
    	$this->assertEquals('foo', $actual->name);
    	$this->assertEquals('', $actual->website);
    	$this->assertEquals('tag1,tag2', $actual->tags);
    }

    public function testSetProfileWithoutWebsiteTags()
    {
    
    	$this->manager->updateProfile(new OrgProfile('foo', '', []));
    
    	$actual = DB::table('orgprofiles')
    		->select(['name','website','tags'])
    		->where('nodeid', '=', $this->node->nodeid())
    		->first();
    
    	$this->assertNotNull($actual);
    	$this->assertEquals('foo', $actual->name);
    	$this->assertEquals('', $actual->website);
    	$this->assertEquals('', $actual->tags);
    }
    public function testUpdateProfile()
    {
    
    	$this->manager->updateProfile(new OrgProfile('foo', 'bar', ['tag1','tag2']));
    	$this->manager->updateProfile(new OrgProfile('foo', 'bar2', ['tag1','tag2','tag3']));
    	
    	$actual = DB::table('orgprofiles')
	    	->select(['name','website','tags'])
	    	->where('nodeid', '=', $this->node->nodeid())
	    	->first();
    
    	$this->assertNotNull($actual);
    	$this->assertEquals('foo', $actual->name);
    	$this->assertEquals('bar2', $actual->website);
    	$this->assertEquals('tag1,tag2,tag3', $actual->tags);
    }
    
    public function testGetProfileSuccess()
    {
    
    	$this->manager->updateProfile(new OrgProfile('foo', 'bar', ['tag1','tag2']));
    	$actual = $this->manager->getProfile();
    	$this->assertNotNull($actual);
    	$this->assertEquals('foo', $actual->name());
    	$this->assertEquals('bar', $actual->website());
    	$this->assertEquals(['tag1','tag2'], $actual->tags());
    }
}
