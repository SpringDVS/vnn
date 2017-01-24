<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CertServiceStoreTest extends TestCase
{
	
	/**
	 * @var \SpringDvs\Core\NetServiceKeyStore
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
	}

    public function testSetNotify()
    {
        
    }
}