<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use SpringDvs\Core\LocalNodeInterface;
use App\Models\LocalNodeLookup;

class SpringNodeController extends Controller
{
	/**
	 * @var LocalNodeLookup The lookup for the virtual node
	 */
	private $lookup;

	public function __construct(LocalNodeLookup $lookup) {
		$this->lookup = $lookup;		
	}
	
	public function spring($regional, $spring) {
		
	}
}
