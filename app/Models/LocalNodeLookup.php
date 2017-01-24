<?php

namespace App\Models;
use App\Models\DbModel;
use App\Models\VirtualNodeModel;

class LocalNodeLookup
extends DbModel {
	
	/**
	 * @var \App\Models\VnnServerModel Vnn Server model
	 */
	private $vnn;
	/**
	 * 
	 * @param VnnServerModel $vnn
	 */
	public function __construct($connection, \App\Models\VnnServerModel $vnn) {
		parent::__construct($connection);
		$this->vnn = $vnn;
	}
	/**
	 * Create a LocalNodeInterface from springname and regional
	 *  
	 * @param string $springname
	 * @param string $regional
	 * @return \SpringDvs\Core\LocalNodeInterface | null 
	 */
	public function fromSpring($springname, $regional, $top = 'uk') {		
		// We have to get the details
		$info = $this->db->table('nodes')
			->join('clusters', 'clusters.nodeid', '=', 'nodes.id')
			->join('regionals', 'regionals.id', '=', 'clusters.netid')
			->select(['nodes.id', 'nodes.springname', 'regionals.network'])
			->where('nodes.springname', $springname, '=')
			->where('regionals.network', $regional, '=')
			->first();

		if($info == null) {
			return null;
		}
		return new VirtualNodeModel($info->id,
									$info->springname,
									$this->vnn->hostname(),
									$this->vnn->hostpath($info->springname, $info->network),
									$info->network,
									$top);
		
	}
}