<?php
namespace App\Models;

use Illuminate\Database\ConnectionInterface;
use SpringDvs\Core\LocalNodeInterface;

abstract class NodeDbModel
extends DbModel {
	protected $localNode;
	
	public function __construct(ConnectionInterface $connection, LocalNodeInterface $localNode) {
		parent::__construct($connection);
		$this->localNode = $localNode;
	}
}