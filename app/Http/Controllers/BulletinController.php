<?php
namespace App\Http\Controllers;
use \SpringDvs\Core\LocalNodeInterface as LocalNodeInterface;
use \SpringDvs\Core\NetServices\BulletinRepositoryInterface as BulletinRepository;
use App\Http\Controllers\Controller;
use SpringDvs\Core\NetServices\BulletinManagerInterface;

class BulletinController extends Controller {
	
	/**
	 * @var \SpringDvs\Core\NetServices\BulletinManagerInterface The bulletin repository
	 */
	private $bulletin;
	
	/**
	 * @var \SpringDvs\Core\LocalNodeInterface The local node
	 */
	private $node;
	
	public function __construct(BulletinManagerInterface $service, LocalNodeInterface $node) {
		$this->bulletin = $service;
		$this->node = $node;
	}

	public function overview() {
		$uri = $this->node->uri();
		return view('bulletin.view', ['uri' => $uri]);
	}
}