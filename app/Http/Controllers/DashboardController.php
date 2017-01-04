<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use SpringDvs\Core\LocalNodeInterface;
use SpringDvs\Core\NetServices\BulletinRepositoryInterface;

class DashboardController extends Controller
{
	/**
	 * @var \SpringDvs\Core\LocalNodeInterface The local node
	 */
	private $node;
	
	private $repo;

	public function __construct(LocalNodeInterface $node, BulletinRepositoryInterface $repo)
	{
		$this->node = $node;
		$this->repo = $repo;
	}


	public function overview()
	{
		
		$this->helperAddBulletin();
		$this->helperAddBulletin(null,null,'Post Title 2');
		$this->helperAddBulletin(null,null,'Post Title 3');
		$this->helperAddBulletin(null,null,'Post Title 4');
		$this->helperAddBulletin(null,null,'Post Title 5');
		$this->helperAddBulletin(null,null,'Post Title 6');
		$this->helperAddBulletin(null,null,'Post Title 7');

		return view('dash.overview', [
					'uri' => $this->node->uri()
				]);
	}
	
	private function helperAddBulletin($tags = null, $cats = null, $title = null) {
	
		$tags = $tags == null ? ['foo','bar'] : $tags;
		$cats = $cats == null ? ['Foo'] : $cats;
		$title = $title == null ? 'Post Title' : $title;
	
		$bulletin = new \SpringDvs\Core\NetServices\Bulletin(
				null,
				$title,
				$tags,
				$cats,
				'Post content');
				return $this->repo->addBulletin($bulletin);
	}
}