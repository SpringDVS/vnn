<?php

namespace App\Models;

use SpringDvs\Core\LocalNodeInterface;

/**
 * Representation of a virtual node in the VNN. This is used when
 * servicing spring requests on the node 
 */
class VirtualNodeModel
implements LocalNodeInterface
{
	
	/**
	 * @var integer The internal node ID
	 */
	private $nodeid;
	
	/**
	 * @var string The springname of the node
	 */
	private $springname;
	
	/**
	 * @var string The hostname associated with the node
	 */
	private $hostname;
	
	/**
	 * @var string The path associated with the node
	 */
	private $hostpath;
	
	/**
	 * @var string The regional spring network
	 */
	private $regional;
	
	/**
	 * @var string The top spring network
	 */
	private $top;
	
	/**
	 * @var \SpringDvs\Node[] An array of primary nodes
	 */
	private $primary;
	
	/**
	 * Create an interface for a virtual node
	 * 
	 * @param integer $nodeid
	 * @param string $springname
	 * @param string $hostname
	 * @param string $hostpath
	 * @param string $regional
	 * @param string $top
	 * @param \SpringDvs\Node[] $primary
	 */
	public function __construct($nodeid, $springname, $hostname, $hostpath, $regional, $top, $primary = array()) {
		$this->springname = $springname;
		$this->hostname = $hostname;
		$this->hostpath = $hostpath;
		$this->regional = $regional;
		$this->top = $top;
		$this->primary = $primary;
		$this->nodeid = $nodeid;
	}

	/**
	 * {@inheritDoc}
	 * @see \SpringDvs\Core\LocalNodeInterface::hostname()
	 */
	public function hostname() {
		return $this->hostname;
	}

	/**
	 * {@inheritDoc}
	 * @see \SpringDvs\Core\LocalNodeInterface::top()
	 */
	public function top() {
		return $this->top;
	}

	/**
	 * {@inheritDoc}
	 * @see \SpringDvs\Core\LocalNodeInterface::regional()
	 */
	public function regional() {
		return $this->regional;
	}

	/**
	 * {@inheritDoc}
	 * @see \SpringDvs\Core\LocalNodeInterface::uri()
	 */
	public function uri() {
		return $this->springname .
			'.' . $this->regional .
			'.' . $this->top;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \SpringDvs\Core\LocalNodeInterface::nodeid()
	 */
	public function nodeid() {
		return $this->nodeid;
	}
	/**
	 * {@inheritDoc}
	 * @see \SpringDvs\Core\LocalNodeInterface::springname()
	 */
	public function springname() {
		return $this->springname;
	}
	/**
	 * {@inheritDoc}
	 * @see \SpringDvs\Core\LocalNodeInterface::hostfield()
	 */
	public function hostfield() {
		return $this->hostname . 
				($this->hostpath != ''
					? '/'.$this->hostpath
					: '');
	}
	/**
	 * {@inheritDoc}
	 * @see \SpringDvs\Core\LocalNodeInterface::hostpath()
	 */
	public function hostpath() {
		return $this->hostpath;
	}
	/**
	 * 
	 * {@inheritDoc}
	 * @see \SpringDvs\Core\LocalNodeInterface::primary()
	 */
	public function primary() {
		return $this->primary;
	}
}