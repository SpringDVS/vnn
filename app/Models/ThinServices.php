<?php
namespace App\Models;

use App\Models\DbModel;

/**
 * Check, if any, which thin service is used by a 
 * node given the module
 */
class ThinServices extends DbModel{
	
	/**
	 * Get the type of thin service (if any)
	 * 
	 * @param string $module The service module
	 * @param integer $nodeid The node ID
	 * @return string|null The name of the thin service
	 */
	public function service($module, $nodeid) {
		return $this->db->table('thin_services')
			->where([
				['nodeid', '=', $nodeid],
				['module', '=', $module],])
			->value('type');
					
	}
	
	/**
	 * Set or update a thin service for a node's module
	 * 
	 * @param string $module The name of the module
	 * @param string $type The type of thin service
	 * @param integer $nodeid The Node's ID
	 */
	public function setService($module, $type, $nodeid) {
		$this->db->table('thin_services')
			->updateOrInsert([
				'module' => $module,
				'nodeid' => $nodeid,],
				
				['type' => $type]
			);
	}
	
	/**
	 * Remove thin service
	 *
	 * @param string $module The name of the module
	 * @param integer $nodeid The Node's ID
	 * @param string $type The type of thin service
	 * @return integer The number of rows affected
	 */
	public function removeService($module, $nodeid) {
		return $this->db->table('thin_services')
			->where([
				['nodeid', '=', $nodeid],
				['module', '=', $module]])
			->delete();
	}
	
	/**
	 * Get a unserialised configuration
	 * 
	 * Each service will have their own configuration
	 * 
	 * @param string $module The name of the module
	 * @param integer $nodeid The node's ID
	 * @return mixed|null The configuration
	 */
	public function getConfig($module, $nodeid) {
		$var = $this->db->table('thin_services')
			->where([
				['nodeid', '=', $nodeid],
				['module', '=', $module]
			])
			->value('config');
			
		if(!$var) { return null; }
		
		return unserialize($var);
	}
	
	/**
	 * Set the modules configuration
	 * 
	 * @param string $module The name of the module
	 * @param integer $nodeid The node's ID
	 * @param mixed $config The configuration to serialise
	 */
	public function setConfig($module, $nodeid, $config) {
		$this->db->table('thin_services')
			->where([
				['nodeid', '=', $nodeid],
				['module', '=', $module]
			])
			->update(['config' => serialize($config)]);
	}
}