<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use SpringDvs\Core\NetServices\Certificate;
use SpringDvs\Core\NetServices\Signature;
use Hamcrest\Core\IsInstanceOf;

class KeyringModelTest extends TestCase
{
	/**
	 * 
	 * @var \SpringDvs\Core\NetServices\KeyringInterface The keyring
	 */
	private $keyring;
	
	
	public function setUp() {
		parent::setUp();
		$netId = DB::table('regionals')->insert(
				['network' => 'venus']
				);
			
		$this->nodeId = DB::table('nodes')->insert(
				['springname' => 'alpha']
				);
			
		DB::table('clusters')->insert(
				['netid' => $netId, 'nodeid' => 1]
				);
			
		DB::table('userassoc')->insert(
				['uid' => 1, 'nodeid' => 1]
				);
			
		$this->keyring = $this->app->make('SpringDvs\Core\NetServices\KeyringInterface');
			
		$_SERVER['HTTP_HOST'] = 'section9';
	}

	public function testAddNewCertificateNoSigsNotOwned()
    {
		$certificate = $this->helperCreateCertificate('foo');
		$this->assertTrue($this->keyring->setCertificate($certificate));
		
		$rows = DB::table('certificates')
						->select('uidname', 'uidemail', 'keyid', 'sigs', 'owned')
						->get();

		$this->assertCount(1, $rows);
		$this->assertRowCheck([
				0 => [
						'uidname' => 'foo', 'uidemail' => 'foo@foo.tld',
						'keyid' => md5('foo'), 'sigs' => '', 'owned' => 0
				]
		], $rows);
    }

    public function testAddNewCertificateNoSigsOwned()
    {
    	$certificate = $this->helperCreateCertificate('foo',[], true);
    	
    	$this->assertTrue($this->keyring->setCertificate($certificate));
    	
    	$rows = DB::table('certificates')
			    	->select('uidname', 'uidemail', 'keyid', 'sigs', 'owned')
			    	->get();
    
    	$this->assertCount(1, $rows);
    	 
    	$this->assertRowCheck([
    			0 => [
    					'uidname' => 'foo', 'uidemail' => 'foo@foo.tld',
    					'keyid' => md5('foo'), 'sigs' => '', 'owned' => 1
    			]
    	], $rows);
    }
    
    public function testAddNewCertificateWithTwoSigsNotOwned()
    {
    	$sigA = md5('A');
    	$sigB = md5('B');
    	
    	$certificate = $this->helperCreateCertificate('foo',[$sigA,$sigB]);
    	$this->assertTrue($this->keyring->setCertificate($certificate));
    
    	$rows = DB::table('certificates')
			    	->select('uidname', 'uidemail', 'keyid', 'sigs', 'owned')
			    	->get();
    
    	$this->assertCount(1, $rows);
    	$this->assertRowCheck([
    			0 => [
    					'uidname' => 'foo', 'uidemail' => 'foo@foo.tld',
    					'keyid' => md5('foo'), 'sigs' => "$sigA,$sigB", 'owned' => 0
    			]
    	], $rows);
    }
    
    public function testAddTwoNewCertificatesNotOwned()
    {
    	$this->helperFillKeyring(2);
    
    	$rows = DB::table('certificates')
    				->select('uidname', 'uidemail', 'keyid', 'sigs', 'owned')
    				->get();
    
    	$this->assertCount(2, $rows);

    	$this->assertRowCheck([
    			0 => [
    					'uidname' => 'foo1', 'uidemail' => 'foo1@foo.tld',
    					'keyid' => md5('foo1'), 'sigs' => $this->helperGraphString(2, 1), 'owned' => 0
    			],
    			1 => [
    					'uidname' => 'foo2', 'uidemail' => 'foo2@foo.tld',
    					'keyid' => md5('foo2'), 'sigs' => $this->helperGraphString(2, 2), 'owned' => 0
    			]
    	], $rows);
    }
    
    public function testGetUidNameSuccess() {
    	$certificate = $this->helperCreateAndAddCertificate('foo1');
    	$this->assertEquals($certificate->name(), $this->keyring->getUidName($certificate->keyid()));
    }
    
    public function testGetUidNameFailure() {
    	$this->helperCreateAndAddCertificate('foo1');    	 
    	$this->assertFalse($this->keyring->getUidName("invalid"));
    }
    
    public function testGetUidNameFailureBadNodeId() {
    	$this->helperCreateAndAddCertificate('foo1');
    	$this->helperChangeNodeId('foo1');
    	$this->assertFalse($this->keyring->getUidName("foo1"));
    }

    public function testGetNodePublicKeySuccess() {
    	$certificate = $this->helperCreateAndAddCertificate('foo1',[],true);
		$this->assertEquals($certificate->armor(), $this->keyring->getNodePublicKey());   
    }

    public function testGetNodePublicKeyFailure() {
    	$this->helperCreateAndAddCertificate('private',[],true, true);
    	$this->assertNull($this->keyring->getNodePublicKey());
    }

    public function testGetNodePublicKeyFailureBadId() {
    	$certificate = $this->helperCreateAndAddCertificate('foo1',[],true);
    	$this->helperChangeNodeId('foo1');
    	$this->assertNull($this->keyring->getNodePublicKey());
    }
    
    public function testHasPublicKeySuccess() {
    	$this->helperCreateAndAddCertificate('foo1',[],true);
    	$this->assertTrue($this->keyring->hasCertificate());
    }
    
    public function testHasPublicKeyFailure() {
    	$this->helperCreateAndAddCertificate('foo1',[]);
    	$this->assertFalse($this->keyring->hasCertificate());
    }
    
    public function testHasPublicKeyFailureBadId() {
    	$this->helperCreateAndAddCertificate('foo1',[],true);
    	$this->helperChangeNodeId('foo1');
    	$this->assertFalse($this->keyring->hasCertificate());
    }
    
    public function testGetNodePrivateKeySuccess() {
    	$this->helperCreateAndAddCertificate('private',[],true, true);
    	$this->assertEquals('privateprivate', $this->keyring->getNodePrivateKey());
    }
    
    public function testGetNodePrivateKeyFailure() {
    	$certificate = $this->helperCreateAndAddCertificate('foo',[]);
    	$this->assertNull($this->keyring->getNodePrivateKey());
    }
    
    public function testGetNodePrivateKeyFailureBadId() {
    	$this->helperCreateAndAddCertificate('private',[],true, true);
    	$this->helperChangeNodeId('private');
    	$this->assertNull($this->keyring->getNodePrivateKey());
    }

    public function testHasPrivateKeySuccess() {
    	$this->helperCreateAndAddCertificate('private',[],true, true);
    	$this->assertTrue($this->keyring->hasPrivateKey());
    }
    
    public function testHasPrivateKeyFailure() {
		$this->helperCreateAndAddCertificate('foo',[]);
    	$this->assertFalse($this->keyring->hasPrivateKey());
    }
    
    public function testHasPrivateKeyFailureBadId() {
    	$this->helperCreateAndAddCertificate('private',[],true, true);
    	$this->helperChangeNodeId('private');
    	$this->assertFalse($this->keyring->hasPrivateKey());
    }

    public function testGetNodeCertificateSuccess() {
    	$expected = $this->helperCreateAndAddCertificate('foo',[],true);
    	$actual = $this->keyring->getNodeCertificate();
    	$this->assertCertificate($expected, $actual); 
    }
    
    public function testGetNodeCertificateFailure() {
    	$expected = $this->helperCreateAndAddCertificate('foo',[]);    	
    	$actual = $this->keyring->getNodeCertificate();
    	$this->assertNull($actual);
    }
    
    public function testGetNodeCertificateFailureBadId() {
    	$expected = $this->helperCreateAndAddCertificate('foo',[],true);
    	$this->helperChangeNodeId('foo');
    	$actual = $this->keyring->getNodeCertificate();
    	$this->assertNull($actual);
    }
    
    public function testGetCertificateNoSigsSuccess() {
    	$expected = $this->helperCreateAndAddCertificate('foo');
    	$actual = $this->keyring->getCertificate($this->helperNameKeyid('foo'));
    	$this->assertCertificate($expected, $actual);
    
    }
    
    public function testGetCertificateWithSigsSuccess() {
    	$expected = $this->helperCreateAndAddCertificate('foo',[md5('bar')]);
    	$actual = $this->keyring->getCertificate($this->helperNameKeyid('foo'));
    	$this->assertCertificate($expected, $actual);
    
    }

    public function testGetCertificateNoSigFailure() {
    	$expected = $this->helperCreateAndAddCertificate('foo',[]);
    	$actual = $this->keyring->getCertificate($this->helperNameKeyid('bar'));
    	$this->assertNull($actual);
    }

    public function testGetResolvedCertificate() {
    	$this->helperFillKeyring(7);
    	$expected = $this->helperCreateCertificate('foo2', $this->helperGenerateResolvedGraph(7, 2));
    	$actual = $this->keyring->getResolvedCertificate($this->helperNameKeyid('foo2'));
    	$this->assertCertificate($expected, $actual);
    
    }
    
    public function testGetResolvedCertificateNoName() {
    	$expected = $this->helperCreateAndAddCertificate('foo',[md5('bar')]);
    	$actual = $this->keyring->getResolvedCertificate($this->helperNameKeyid('foo'));
    	$this->assertCertificate($expected, $actual);
    
    }

    public function testGetResolvedCertificateOneInvalidName() {
    	$this->helperCreateAndAddCertificate('foo',[md5('bar'), md5('invalid')]);
    	$this->helperCreateAndAddCertificate('bar');
    	$expected = $this->helperCreateCertificate('foo',[
    										new Signature(md5('bar'),'bar'),
    										new Signature(md5('invalid'))
    										]);
    	$actual = $this->keyring->getResolvedCertificate($this->helperNameKeyid('foo'));
    	$this->assertCertificate($expected, $actual);
    }
    
    public function testGetResolvedCertificateBadId() {
    	$this->helperFillKeyring(7);
    	$this->helperChangeNodeId('foo2');
    	$actual = $this->keyring->getResolvedCertificate($this->helperNameKeyid('foo2'));
    	$this->assertNull($actual);
    
    }
    
    public function testGetKeySuccess() {
    	$this->helperFillKeyring(7);
    	$actual = $this->keyring->getKey($this->helperNameKeyid('foo2'));
    	$this->assertEquals($this->helperArmor('foo2'), $actual);
    }
    
    public function testGetKeyFailure() {
    	$this->helperFillKeyring(7);
    	$actual = $this->keyring->getKey($this->helperNameKeyid('foo101'));
    	$this->assertNull($actual);
    }
    
    public function testGetKeyFailureBadId() {
    	$this->helperFillKeyring(7);
    	$this->helperChangeNodeId('foo2');
    	$actual = $this->keyring->getKey($this->helperNameKeyid('foo2'));
    	$this->assertNull($actual);
    }
    
    public function testGetNodeKeySuccess() {
    	$this->helperFillKeyring(7);
    	$this->helperCreateAndAddCertificate('foo1000',[],true);
    	$actual = $this->keyring->getNodeKeyid();
    	$this->assertEquals($this->helperNameKeyid('foo1000'), $actual);
    }
    
    public function testGetNodeKeyFailureBadId() {
    	$this->helperFillKeyring(7);
    	$this->helperCreateAndAddCertificate('foo1000',[],true);
    	$this->helperChangeNodeId('foo1000');
    	$actual = $this->keyring->getNodeKeyid();
    	$this->assertNull($actual);
    }
    
    public function testRemoveCertificateSuccess() {
    	$this->helperFillKeyring(7);
    	$keyid = $this->helperNameKeyid('foo2');
    	$this->assertTrue($this->keyring->removeCertificate($keyid));
    	$this->assertNull($this->keyring->getCertificate($keyid));
    }
    
    public function testRemoveCertificateFailure() {
    	$this->helperFillKeyring(7);
    	$keyid = $this->helperNameKeyid('foo200');
    	$this->assertFalse($this->keyring->removeCertificate($keyid));
    }
    
    public function testRemoveCertificateFailureBadId() {
    	$this->helperFillKeyring(7);
    	$this->helperChangeNodeId('foo2');
    	
    	$keyid = $this->helperNameKeyid('foo2');
    	$this->assertFalse($this->keyring->removeCertificate($keyid));
    }
    
    public function testRemoveOwnedPublicCertificate() {
    	$this->helperFillKeyring(7);
    	$this->helperCreateAndAddCertificate('foo100',[],true);
    	$keyid = $this->helperNameKeyid('foo100');
    	$this->assertFalse($this->keyring->removeCertificate($keyid));
    }
    
    public function testRemovePrivateKeyFailure() {
    	$this->helperFillKeyring(7);
    	$this->helperCreateAndAddCertificate('foo100',[],true,true);
    	$this->assertFalse($this->keyring->removeCertificate('private'));
    }
    
    public function testRemovePrivateKeyFailureBadId() {
    	$this->helperFillKeyring(7);
    	$this->helperCreateAndAddCertificate('foo100',[],true,true);
    	$this->helperChangeNodeId('foo100');
    	$this->assertFalse($this->keyring->removeCertificate('private'));
    }
    
    public function testCertificateUpdate() {
    	$this->helperFillKeyring(7);
    	$signatures = $this->helperGenerateGraph(7, 4);
    	
    	
    	$expected = $this->helperCreateAndAddCertificate('foo4', $signatures);
    	$this->assertCertificate($expected, $this->keyring->getCertificate($expected->keyid()));
    	
    	$signatures[] = $this->helperNameKeyid('bar');
    	$expected = $this->helperCreateAndAddCertificate('foo4', $signatures);
    	
    	$this->keyring->setCertificate($expected);
    	$this->assertCertificate($expected, $this->keyring->getCertificate($expected->keyid()));
    }
    
    public function testCertificateUpdateNoUpdate() {
    	$this->helperFillKeyring(7);
    	$signatures = $this->helperGenerateGraph(7, 4);
    	 
    	 
    	$expected = $this->helperCreateAndAddCertificate('foo4', $signatures);
    	$this->assertCertificate($expected, $this->keyring->getCertificate($expected->keyid()));

    	$this->keyring->setCertificate($expected);
    	$this->assertCertificate($expected, $this->keyring->getCertificate($expected->keyid()));
    }
    
    public function testCertificateUpdateBadId() {
    	$this->helperFillKeyring(7);
    	$this->helperChangeNodeId('foo4');
    	
    	$this->assertEquals(6, $this->keyring->getCertificateCount());
    	$signatures = $this->helperGenerateGraph(7, 4);
    	  
    	$expected = $this->helperCreateCertificate('foo4', $signatures);
   		    
    	$this->assertTrue($this->keyring->setCertificate($expected));
    	$this->assertEquals(7, $this->keyring->getCertificateCount());
    }
    
    public function testSetCertificateWithPrivateKeyid() {
    	$certificate = $this->helperCreateAndAddCertificate('private',[],true,true);
    	$this->assertFalse($this->keyring->setCertificate($certificate));    
    }
    
    public function testSetNodeCertificate() {
    	$certificate  = $this->helperCreateCertificate('alpha.venus.uk',[],true);
    	$this->assertTrue($this->keyring->setNodeCertificate($certificate));
    	$this->assertCertificate($certificate, $this->keyring->getNodeCertificate());
    }
    
    public function testSetNodeCertificateNotNode() {
    	$certificate  = $this->helperCreateCertificate('beta.venus.uk',[],true);
    	$this->assertFalse($this->keyring->setNodeCertificate($certificate));
    }
    
    public function testUpdateNodeCertificate() {
    	$certificate  = $this->helperCreateCertificate('alpha.venus.uk',[],true);
    	$this->assertTrue($this->keyring->setNodeCertificate($certificate));
    	$certificate  = $this->helperCreateCertificate('alpha.venus.uk',[md5('bar')],true);
    	$this->assertTrue($this->keyring->setNodeCertificate($certificate));
    	$this->assertCertificate($certificate, $this->keyring->getNodeCertificate());
    }
   
    public function testUpdateNodeCertificateBadId() {
    	$certificate  = $this->helperCreateCertificate('alpha.venus.uk',[],true);
    	$this->assertTrue($this->keyring->setNodeCertificate($certificate));
    	$this->helperChangeNodeId('alpha.venus.uk');
    	$this->assertFalse($this->keyring->setNodeCertificate($certificate));
    }
    
    public function testSetNodeCertificateIsPrivate() {
    	$certificate  = $this->helperCreateCertificate('beta.venus.uk',[],true,true);
    	$this->assertFalse($this->keyring->setNodeCertificate($certificate));
    }
    
    public function testSetNodePrivate() {
    	$key  = $this->helperCreateCertificate('private',[],true,true);
    	$this->assertTrue($this->keyring->setNodePrivate($key));
    	$this->assertEquals($key->armor(), $this->keyring->getNodePrivateKey());
    }
        
    public function testSetNodePrivateWithKeyInplace() {
    	$key  = $this->helperCreateCertificate('private',[],true,true);
    	$this->assertTrue($this->keyring->setNodePrivate($key));
    	$this->assertFalse($this->keyring->setNodePrivate($key));
    }
    
    public function testResetNodeKeys() {
    	$key  = $this->helperCreateCertificate('private',[],true,true);
    	$certificate  = $this->helperCreateCertificate('alpha.venus.uk',[],true);
    	$this->assertTrue($this->keyring->setCertificate($certificate));
    	$this->assertTrue($this->keyring->setNodePrivate($key));
    	
    	$this->assertTrue($this->keyring->resetNodeKeys());
    	$this->assertNull($this->keyring->getNodeCertificate());
    	$this->assertNull($this->keyring->getNodePrivateKey());
    }
    
    public function testResetNodeKeysBadId() {
    	$key  = $this->helperCreateCertificate('private',[],true,true);
    	$certificate  = $this->helperCreateCertificate('alpha.venus.uk',[],true);
    	$this->helperChangeNodeId('private');
    	$this->helperChangeNodeId('neta.venus.uk');
    	$this->assertFalse($this->keyring->resetNodeKeys());
    }
    
    public function testUidListZeroListing() {
    	$list = $this->keyring->getUidList(1,5);
    	$this->assertCount(0, $list);
    }
    
    public function testUidList() {
    	$this->helperFillKeyring(8);
    	
    	$list = $this->keyring->getUidList(1,3);
    	
    	$this->assertCount(3, $list);
    	$i = 1;
    	foreach($list as $certificate) {
    		$this->assertEquals($this->helperNameKeyid("foo$i"), $certificate->keyid());
    		$i++;
    	}
    	
    	$list = $this->keyring->getUidList(2,3);
    	$this->assertCount(3, $list);
    	foreach($list as $certificate) {
    		$this->assertEquals($this->helperNameKeyid("foo$i"), $certificate->keyid());
    		$i++;
    	}
    	
    	$list = $this->keyring->getUidList(3,3);
    	$this->assertCount(2, $list);
    	foreach($list as $certificate) {
    		$this->assertEquals($this->helperNameKeyid("foo$i"), $certificate->keyid());
    		$i++;
    	}
    }
    
    public function testGetCertificateCountNoPrivate() {
    	$this->helperFillKeyring(30);
    	$this->assertEquals(30, $this->keyring->getCertificateCount());
    }
    
    public function testGetCertificateCountWithPrivate() {
    	$this->helperFillKeyring(29);
    	$this->helperCreateAndAddCertificate('private',[],true,true);
    	$this->assertEquals(29, $this->keyring->getCertificateCount());
    }
    
	
    
    /**
     * Create a certificate object with defaults for testing
     * 
     * @param unknown $name
     * @param array $signatures
     * @param string $owned
     * @param string $private
     * @return \SpringDvs\Core\NetServices\Certificate
     */
    private function helperCreateCertificate($name, $signatures = [], $owned = false, $private = false) {
		$email = $name . '@foo.tld';
		$keyid = !$private ? $this->helperNameKeyid($name) : 'private';
		$armor = $name.$keyid; // just use this for armor for testing
		$sigs = [];
		foreach($signatures as $sig) {
			if( $sig instanceof Signature){
				$sigs[] = $sig;
			} else {
				$sigs[] = new Signature($sig);
			}
		}
		return new Certificate($armor,$owned, $name, $email, $keyid, $sigs);	
    }
    
    /**
     * Create and automatically add certificate to keyring
     * @param unknown $name
     * @param array $signatures
     * @param string $owned
     * @param string $private
     * @return Certificate
     */
    private function helperCreateAndAddCertificate($name, $signatures = [], $owned = false, $private = false) {
    	$certificate = $this->helperCreateCertificate($name, $signatures, $owned, $private);
    	if($private) {
    		$this->assertTrue($this->keyring->setNodePrivate($certificate));
    	} else {
    		$this->assertTrue($this->keyring->setCertificate($certificate));
    	}
    	return $certificate;
    }
    
    /**
     * Automatically fill a keyring with a set number of certificates
     * @param unknown $size
     * @param array $jokers
     */
    private function helperFillKeyring($size, $jokers = []) {
    	for($i = 1; $i <= $size; $i++) {

    		$name = "foo{$i}";

    		
    		$owned = isset($jokers[$i]['owned'])
    					? $jokers[$i]['owned']
    					: false;
    		
    		$private = isset($jokers[$i]['private'])
    					? $jokers[$i]['owned']
    					: false;

    		$sigs = [];
    		if(!$private) {
    			$sigs = isset($jokers[$i]['sigs'])
	    			? $jokers[$i]['sigs']
	    			: $this->helperGenerateGraph($size, $i);
    		}
			$this->helperCreateAndAddCertificate($name, $sigs, $owned, $private);
    	}
    }
    
    /**
     * Generate a signature graph with other keys in keyring
     * 
     * @param unknown $size
     * @param unknown $i
     * @return string[]
     */
    private function helperGenerateGraph($size, $i) {
    	$graph = [];
    	for($j = 1; $j <= $size; $j++) {
    		if($j == $i){ continue; }
    		$graph[] = $this->helperNameKeyid("foo$j");
    	}
    	
    	return $graph;
    }
    
    /**
     * Generate a signature graph with other keys in keyring and resolved name
     *
     * @param unknown $size
     * @param unknown $i
     * @return Signature[]
     */
    private function helperGenerateResolvedGraph($size, $i) {
    	$graph = [];
    	for($j = 1; $j <= $size; $j++) {
    		if($j == $i){ continue; }
    		$name = "foo$j";
    		$graph[] = new Signature($this->helperNameKeyid($name), $name);
    	}
    	 
    	return $graph;
    }
    
    private function helperGraphString($size, $i) {
    	return implode(',', $this->helperGenerateGraph($size, $i));
    }
		
    private function helperNameKeyid($name) {
    	return md5($name);
    }
    
    private function helperArmor($name) {
    	return $name.$this->helperNameKeyid($name);
    }
    
    private function helperChangeNodeId($name, $nodeid = 2) {
    	DB::table('certificates')->where('uidname','=',$name)
    					->update(['nodeid' => $nodeid]);
    }
    
    private function assertRowCheck($expected, $rows) {
    	foreach($rows as $i => $row) {
    		if(isset($expected[$i])) {
    			foreach($expected[$i] as $col => $val) {
    				$this->assertEquals($val, $row->$col);
    			}
    		}
    	}
    }
    
    
    private function assertCertificate(Certificate $expected, Certificate $actual) {
    	$this->assertNotNull($actual);
    	$this->assertEquals($expected->keyid(), $actual->keyid());
    	$this->assertEquals($expected->name(), $actual->name());
    	$this->assertEquals($expected->email(), $actual->email());
    	$this->assertEquals($expected->owned(), $actual->owned());
    	
    	$expectedSignatures = $expected->signatures();
    	$actualSignatures = $actual->signatures();

    	$count = count($expectedSignatures);
    	$this->assertCount($count, $actualSignatures);
    	
    	for($i = 0; $i < $count; $i++) {
    		$this->assertEquals($expectedSignatures[$i]->name, $actualSignatures[$i]->name);
    		$this->assertEquals($expectedSignatures[$i]->keyid, $actualSignatures[$i]->keyid);
    		
    	}
    	
    	$this->assertEquals($expected->armor(), $actual->armor());
    }

}
