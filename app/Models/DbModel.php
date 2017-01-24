<?php
namespace App\Models;
use Illuminate\Database\ConnectionInterface;

abstract class DbModel {
	/**
	 * Database
	 * @var \Illuminate\Database\ConnectionInterface
	 */
	protected $db;
	
	public function __construct(ConnectionInterface $connection)
	{
		$this->db = $connection;
	}
	
}