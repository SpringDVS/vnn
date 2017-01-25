<?php
namespace App\Models;
use SpringDvs\Core\NetServices\CertKeyStoreInterface;
use Illuminate\Database\ConnectionInterface;
use SpringDvs\Core\LocalNodeInterface;

class CertServiceKeyStore
extends NodeDbModel
implements CertKeyStoreInterface 
{
	private $queryBase;
	private $updateBase;
	
	public function __construct(ConnectionInterface $connection, LocalNodeInterface $localNode) {
		parent::__construct($connection, $localNode);
		$this->queryBase = $this->db->table('keystore')
			->where([
				['nodeid', '=', $this->localNode->nodeid()],
				['module', '=', 'cert']
			]);
	}
	
	public function notify($value = null) {
		return $value == null
			? $this->get('notify') > 0 ? true : false
			: $this->set('notify', $value);
	}
	
	public function pullreqaction($value = null) {
		return $value == null
			? $this->get('pullreqaction')
			: $this->set('pullreqaction', $value);
	}
	
	private function get($key) {
		if(!$this->queryBase->where('key','=',$key)->exists()) {
			return null;
		}
		
		return $this->queryBase
			->where('key', '=', $key)
			->value('value');
	}
	
	private function set($key, $value) {
		if(!$this->queryBase->where('key','=',$key)->exists()) {
			return $this->queryBase
				->insert([
					'nodeid' => $this->localNode->nodeid(),
					'key' => $key,
					'value' => $value,
					'module' => 'cert',
				]);
				
		}
		
		return $this->queryBase
			->where('key', '=', $key)
			->update(['value' => $value]);
		
	}
}