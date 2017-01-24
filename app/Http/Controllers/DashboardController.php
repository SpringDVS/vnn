<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use SpringDvs\Core\LocalNodeInterface;
use SpringDvs\Core\NetServices\BulletinRepositoryInterface;
use SpringDvs\Core\NetServices\BulletinManagerInterface;

class DashboardController extends Controller
{
	/**
	 * @var \SpringDvs\Core\LocalNodeInterface The local node
	 */
	private $node;
	
	public function __construct(LocalNodeInterface $node)
	{
		$this->node = $node;
	}


	public function overview()
	{
		return view('dash.overview', [
					'uri' => $this->node->uri()
				]);
	}
	

}