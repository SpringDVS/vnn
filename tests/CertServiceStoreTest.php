<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\CertServiceKeyStore;
use App\Models\VirtualNodeModel;

class CertServiceStoreTest extends TestCase
{
	
	/**
	 * @var \App\Models\CertServiceKeyStore
	 */
	private $ks;
	
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
		
		$this->ks = $this->app->make(CertServiceKeyStore::class,
			['localNode' => new VirtualNodeModel(1, 'alpha', '', '', 'venus', 'uk')]);
	}

    public function testSetNotifyTrue()
    {
        $this->ks->notify(true);
        $actual = DB::table('keystore')->where([
        	['nodeid','=', 1],
        	['module', '=', 'cert'],
        	['key', '=', 'notify'],
        ])->value('value');
        $this->assertEquals(1, $actual);
    }
    
    public function testSetNotifyFalse()
    {
    	$this->ks->notify(false);
    	$actual = DB::table('keystore')->where([
    			['nodeid','=', 1],
    			['module', '=', 'cert'],
    			['key', '=', 'notify'],
    	])->value('value');
    	$this->assertEquals(0, $actual);
    }
    
    public function testResetNotifyFalseTrue()
    {
    	$this->ks->notify(false);
    	$this->ks->notify(true);
    	$actual = DB::table('keystore')->where([
    			['nodeid','=', 1],
    			['module', '=', 'cert'],
    			['key', '=', 'notify'],
    	])->value('value');
    	$this->assertEquals(1, $actual);
    }
    
    public function testGetNotifySuccess()
    {
    	$this->ks->notify(true);
    	$this->assertEquals(1, $this->ks->notify());
    }
    
    public function testSetPullReqAction()
    {
    	$this->ks->pullreqaction('foo');
    	$actual = DB::table('keystore')->where([
    			['nodeid','=', 1],
    			['module', '=', 'cert'],
    			['key', '=', 'pullreqaction'],
    	])->value('value');
    	$this->assertEquals('foo', $actual);
    }
    
    
    public function testResetPullReqAction()
    {
    	$this->ks->pullreqaction('foo');
    	$this->ks->pullreqaction('bar');
    	$actual = DB::table('keystore')->where([
    			['nodeid','=', 1],
    			['module', '=', 'cert'],
    			['key', '=', 'pullreqaction'],
    	])->value('value');
    	$this->assertEquals('bar', $actual);
    }
    
    public function testGetPullReqActionSuccess()
    {
    	$this->ks->pullreqaction('foobar');
    	$this->assertEquals('foobar', $this->ks->pullreqaction());
    }
}