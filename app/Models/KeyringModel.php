<?php
namespace App\Models;

use \Illuminate\Database\ConnectionInterface as Connection;
use \SpringDvs\Core\NetServices\Certificate as Certificate;
use SpringDvs\Core\NetServices\Key as Key;
use phpDocumentor\Reflection\Types\Integer;
use SpringDvs\Core\NetServices\Signature;

class KeyringModel
implements \SpringDvs\Core\NetServices\KeyringInterface
{
	/**
	 * Database
	 * @var \Illuminate\Database\ConnectionInterface
	 */
	private $db;

	/**
	 * @var \SpringDvs\Core\LocalNodeInterface The node model
	 */
	private $node;

	public function __construct(Connection $connection, \SpringDvs\Core\LocalNodeInterface $nodeModel)
	{
		$this->db = $connection;
		$this->node = $nodeModel;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \SpringDvs\Core\NetServices\KeyringInterface::getNodePublicKey()
	 */
	public function getNodePublicKey() {
		return $this->selectCertificate([
								['owned',  '=',  1],
								['keyid',  '!=', 'private'],
							], ['armor'])->value('armor');
	}

	/**
	 * {@inheritDoc}
	 * @see \SpringDvs\Core\NetServices\KeyringInterface::getNodePrivateKey()
	 */
	public function getNodePrivateKey() {
		return $this->selectCertificate([
									['owned',  '=',  1],
									['keyid',  '=', 'private'],
								], ['armor'])->value('armor');
	}
	
	/**
	 * {@inheritDoc}
	 * @see \SpringDvs\Core\NetServices\KeyringInterface::getNodeCertificate()
	 */
	public function getNodeCertificate() {
		return $this->fillCertificate(
				$this->selectCertificate([
						['owned', '=',   1],
						['keyid', '!=', 'private']
				])->first());
	}
	

	/**
	 * {@inheritDoc}
	 * @see \SpringDvs\Core\NetServices\KeyringInterface::getNodeKeyid()
	 */
	public function getNodeKeyid() {
		return $this->selectCertificate([
						['owned', '=', 1],
						['keyid','!=', 'private']
					],['keyid'])
					->value('keyid');
	}
	

	/**
	 * {@inheritDoc}
	 * @see \SpringDvs\Core\NetServices\KeyringInterface::resetNodeKeys()
	 */
	public function resetNodeKeys(){
		return $this->db->table('certificates')
						->where('owned','=', 1)
						->delete() > 0 ? true: false;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \SpringDvs\Core\NetServices\KeyringInterface::setNodeCertificate()
	 */
	public function setNodeCertificate(Certificate $certificate) {
		
		if($certificate->keyid() == 'private'
		|| $certificate->name() != $this->node->uri()){ 
			return false; 
		}
		
		
		return $this->setCertificate($certificate);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \SpringDvs\Core\NetServices\KeyringInterface::setNodePrivate()
	 */
	public function setNodePrivate(Key $key) {

		if($this->hasPrivateKey()){ return false; }
		
		return $this->db->table('certificates')
						->insert([
							'nodeid' => $this->node->nodeid(),
							'keyid' => 'private',
							'uidname' => 'private',
							'uidemail' => 'private',
							'sigs' => '',
							'armor' => $key->armor(),
							'owned' => 1
						]) == 1 ? true : false;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \SpringDvs\Core\NetServices\KeyringInterface::setCertificate()
	 */
	public function setCertificate(Certificate $certificate) {

		if($certificate->keyid() == 'private'){ return false; }
		
		if($this->getUidName($certificate->keyid())) {
			return $this->db->table('certificates')
					->where([
							['keyid','=',$certificate->keyid()],
							['nodeid','=', $this->node->nodeid()]
					])
					->update([
							'sigs' => $certificate->signatureString(),
							'armor' => $certificate->armor(),
					]) == 1 ? true : false;
		} else {
			if($this->ownedDuplicationGuard($certificate->name())) {
				return false;
			}
			return $this->db->table('certificates')
					->insert([
						'nodeid' => $this->node->nodeid(),
						'keyid' => $certificate->keyid(),
						'uidname' => $certificate->name(),
						'uidemail' => $certificate->email(),
						'sigs' => $certificate->signatureString(),
						'armor' => $certificate->armor(),
						'owned' => $certificate->owned() ? 1 : 0
					]);
		}
	}
	
	/**
	 * {@inheritDoc}
	 * @see \SpringDvs\Core\NetServices\KeyringInterface::getCertificate()
	 */
	public function getCertificate($keyid) {
		return $this->fillCertificate(
							$this->selectCertificate(['keyid' => $keyid])->first()
					);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \SpringDvs\Core\NetServices\KeyringInterface::removeCertificate()
	 */
	public function removeCertificate($keyid) {
		if($keyid == 'private'){ return false; }
		
		return $this->db->table('certificates')
						->where('keyid', '=', $keyid)
						->where('owned', '!=', 1)
						->where('nodeid', '=', $this->node->nodeid())
						->delete() == 1 ? true : false;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \SpringDvs\Core\NetServices\KeyringInterface::getKey()
	 */
	public function getKey($keyid) {
		return $this->selectCertificate(['keyid' => $keyid],['armor'])->value('armor');
	}
	
	/**
	 * {@inheritDoc}
	 * @see \SpringDvs\Core\NetServices\KeyringInterface::getResolvedCertificate()
	 */
	public function getResolvedCertificate($keyid) {
		return $this->fillCertificate($this->selectCertificate(['keyid' => $keyid])->first(), true);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \SpringDvs\Core\NetServices\KeyringInterface::getUidList()
	 */
	public function getUidList($page, $limit = 10) {
		$certificates  = [];
		$rows = $this->selectCertificate([],['uidname', 'keyid', 'uidemail'])
					->orderBy('uidname', 'asc')
					->limit($limit)
					->offset( ($page - 1) * $limit)
					->get();
		foreach($rows as $cols) {
			$certificates[] = $this->fillListing($cols);
		}
		
		return $certificates;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \SpringDvs\Core\NetServices\KeyringInterface::getUidName()
	 */
	public function getUidName($keyid) {
		if($keyid == 'private') {
			return false;
		}
		
		$name =  $this->db->table('certificates')
					->where([
						['keyid','=',$keyid],
						['nodeid','=', $this->node->nodeid()]
					])
					->value('uidname');
		
		if(!count($name)) {
			return false;
		}
		
		return $name;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \SpringDvs\Core\NetServices\KeyringInterface::hasPrivateKey()
	 */
	public function hasPrivateKey() {
		return $this->getNodePrivateKey() ? true : false;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \SpringDvs\Core\NetServices\KeyringInterface::hasCertificate()
	 */
	public function hasCertificate() {
		return $this->getNodePublicKey() ? true : false; 
	}
	
	/**
	 * {@inheritDoc}
	 * @see \SpringDvs\Core\NetServices\KeyringInterface::getCertificateCount()
	 */
	public function getCertificateCount() {
		$count = $this->selectCertificate([],'keyid')
						->count();
		if($this->hasPrivateKey()){ $count--; }
		
		return $count;
	}
	
	private function fillCertificate($cols, $resolve = false) {
		if(!$cols){ return null; }
		return new Certificate(
				$cols->armor,
				$cols->owned == 1 ? true : false,
				$cols->uidname,
				$cols->uidemail,
				$cols->keyid,
				$this->expandSignatures($cols->sigs, $resolve)
		);
	}
	
	private function fillListing($cols, $resolve = false) {
		if(!$cols){ return null; }
		return new Certificate(
				"",
				false,
				$cols->uidname,
				$cols->uidemail,
				$cols->keyid,
				[]
				);
	}
	
	private function expandSignatures($str, $resolve = false) {

		$sigs = [];

		if($str == ''){ return $sigs; }

		foreach(explode(',', $str) as $keyid) {
			$name = 'unknown';
			if($resolve && ($r = $this->getUidName($keyid))) {
				$name = $r;
			}

			$sigs[] = new Signature($keyid, $name);
		}
		
		return $sigs;
	}
	
	private function selectCertificate($where = [], $cols = []) {
		$cols = $cols == []
				? ['uidname', 'uidemail', 'keyid', 'sigs', 'owned','armor']
				: $cols;
		
		if(!isset($where['nodeid'])){
			$where['nodeid'] = $this->node->nodeid();
		}
		return $this->db->table('certificates')
						->select($cols)
						->where($where);
	}
	
	private function ownedDuplicationGuard($name) {
		$v = $this->db->table('certificates')
					->select('keyid')
					->where('uidname', '=', $name)
					->where('owned', '=',1)
					->where('nodeid', '!=', $this->node->nodeid())
					->get();
		if(count($v) > 0) return true;
		
		return false;
	}
}