<?php

namespace App\Models;
use \SpringDvs\Core\LocalNodeInterface as LocalNodeInterface;
use \Illuminate\Database\ConnectionInterface as Connection;

class LocalNodeModel
implements LocalNodeInterface
{
	/**
	 * @var \Illuminate\Database\ConnectionInterface The db connection
	 */
	private $db;
	
	/**
	 * @var integer The UID of the current user account
	 */
	private $uid;
	

	/**
	 * @var string The springname cached from database
	 */
	private $cacheSpringname = null;

	
	/**
	 * @var string The regional network cached from database
	 */
	private $cacheRegional = null;
	
	/**
	 * @var string The URI of node cached from database
	 */
	private $cacheUri = null;
	
	/**
	 * @var integer The node ID cached from the database
	 */
	private $cacheNodeId = null;
	
	
	/**
	 * @var string The top network
	 */
	private $cacheTop = null;

	public function __construct(Connection $connection, $uid) {
		$this->db = $connection;
		$this->uid = $uid;
		$this->cacheTop = 'uk';
	}

	/**
	 * Get the Springname of the node associated with the user
	 * 
	 * This method reads once from the database
	 * 
	 * {@inheritDoc}
	 * @see \SpringDvs\Core\LocalNodeInterface::springname()
	 * 
	 * @return string The Springname of the node
	 */
	public function springname() {
		
		if($this->cacheSpringname) {
			// We already have the springname
			return $this->cacheSpringname;
		}
		
		
		// We have to get the details
		$info = $this->db->table('nodes')
						->join('userassoc', 'userassoc.nodeid', '=', 'nodes.id')
						->select(['nodes.id', 'nodes.springname'])
						->where('userassoc.uid', $this->uid, '=')
						->first();
		
		if(!$this->cacheNodeId){ $this->cacheNodeId = $info->id; }

		$this->cacheSpringname = $info->springname;
		return $info->springname;
	}

	/**
	 * Get the regional network of the current node
	 * 
	 * This method reads once from the database
	 * 
	 * {@inheritDoc}
	 * @see \SpringDvs\Core\LocalNodeInterface::regional()
	 * 
	 * @return string The regional network
	 */
	public function regional() {
		if($this->cacheRegional) {
			// We have already requested this information
			return $this->cacheRegional;
		}

		if(!$this->cacheNodeId) {
			// We need to query against the user ID + cache node ID as well
			$info = $this->db->table('regionals')
							->join('clusters','clusters.netid','regionals.id')
							->join('userassoc', 'userassoc.nodeid', '=', 'clusters.nodeid')
							->select(['regionals.network as network', 'userassoc.nodeid as nodeid'])
							->where('userassoc.uid', $this->uid, '=')
							->first();
							
			$this->cacheNodeId = $info->nodeid;
			$this->cacheRegional = $info->network;
			return $info->network;
		}
		
		// We have the node ID already
		$info = $this->db->table('regionals')
		->join('clusters','clusters.netid','regionals.id')
		->select('regionals.network')
		->where('clusters.nodeid', $this->cacheNodeId, '=')
		->first();
		
		$this->cacheRegional = $info->network;
		return $info->network;
	}
	
	/**
	 * Get the top regional cluster of the node
	 * 
	 * {@inheritDoc}
	 * @see \SpringDvs\Core\LocalNodeInterface::top()
	 * 
	 * @return string The top regional network
	 */
	public function top() {
		return $this->cacheTop;
	}

	/**
	 * Get the Spring URI of the node
	 * 
	 * {@inheritDoc}
	 * @see \SpringDvs\Core\LocalNodeInterface::uri()
	 * 
	 * @return string The URI
	 */
	public function uri() {
		return $this->springname() .
				'.' . $this->regional() .
				'.' . $this->top();
	}

	/**
	 * Get the hostname of the node
	 * 
	 * This is the HTTP service layer hostname used to access
	 * the node via the web
	 * 
	 * e.g. 'example.com/foo/spring' hostname is 'example.com'
	 * {@inheritDoc}
	 * @see \SpringDvs\Core\LocalNodeInterface::hostname()
	 * 
	 * @return string The hostname
	 */
	public function hostname() {
		return $_SERVER['HTTP_HOST'];
	}

	/**
	 * Get the host path of the node
	 * 
	 * This is the path that is used to find the point of service
	 * for the node through the web, without the final /spring
	 * resource
	 * 
	 * e.g. 'example.com/foo/spring' hostpath is '/foo'
	 * 
	 * {@inheritDoc}
	 * @see \SpringDvs\Core\LocalNodeInterface::hostpath()
	 * 
	 * @return string The hostpath
	 */
	public function hostpath() {
		return '/virt/' . $this->regional() . '/' . $this->springname();
	}

	/**
	 * Get the hostfield of the node
	 * 
	 * The hostfield is the hostname and hostpath concatinated
	 * 
	 * e.g. 'example.com/foo/spring' hostfield is 'example.com/foo'
	 * {@inheritDoc}
	 * @see \SpringDvs\Core\LocalNodeInterface::hostfield()
	 * 
	 * @return string The hostfield
	 */
	public function hostfield() {
		return $this->hostname() . $this->hostpath();
	}
	
	/**
	 * {@inheritDoc}
	 * @see \SpringDvs\Core\LocalNodeInterface::nodeid()
	 */
	public function nodeid() {
		if(!$this->cacheNodeId){ $this->springname(); }
		
		return $this->cacheNodeId;
	}
	
	/**
	 * 
	 * {@inheritDoc}
	 * @see \SpringDvs\Core\LocalNodeInterface::primary()
	 */
	public function primary() {
		return [];
	}
}