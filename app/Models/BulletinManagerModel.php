<?php
namespace App\Models;

use \SpringDvs\Core\NetServices\Bulletin as Bulletin;
use SpringDvs\Core\NetServices\BulletinManagerInterface;

class BulletinManagerModel
extends NodeDbModel
implements BulletinManagerInterface
{

	/*
	 * {@inheritDoc}
	 * @see \SpringDvs\Core\NetServices\BulletinManagerInterface::withFilters()
	 *
	 * @return \SpringDvs\Core\NetServices\BulletinHeader[] Array of bulletin headers
	 */
	public function withFilters(array $filters = array())
	{
		$limit = isset($filters['limit']) ? $filters['limit'] : 5;
		unset($filters['limit']);
		
		if(empty($filters)) {
		 	return $this->emptyFilters($limit);
		}
		
		return $this->usingFilters($filters, $limit);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \SpringDvs\Core\NetServices\BulletinManagerInterface::withUid()
	 * 
	 * @return \SpringDvs\Core\NetServices\Bulletin|null Bulletin if found or null if invalid UID
	 */
	public function withUid($uid, $attributes = [])
	{
		$result = $this->db->table('nsbulletins')
					->select('nsbulletins.id',
							 'nsbulletins.title',
							 'nsbulletins.content',
							 $this->db->raw('GROUP_CONCAT(DISTINCT nsbulletin_tags.tag) as tags'),
							 $this->db->raw('GROUP_CONCAT(DISTINCT nsbulletin_categories.category) as categories'))
					->join('nsbulletin_post_tags', 'nsbulletin_post_tags.postid', '=', 'nsbulletins.id')
					->join('nsbulletin_tags', 'nsbulletin_post_tags.tagid', '=', 'nsbulletin_tags.id')
					->join('nsbulletin_post_cats', 'nsbulletin_post_cats.postid', '=', 'nsbulletins.id')
					->join('nsbulletin_categories', 'nsbulletin_post_cats.catid', '=', 'nsbulletin_categories.id')
					->where('nsbulletins.id', '=', $uid)
					->where('nsbulletins.nodeid', '=', $this->localNode->nodeid())
					->first();
					
					

		if($result->id == null){ return null; }

		return new \SpringDvs\Core\NetServices\Bulletin(
								$result->id, 
								$result->title,
								explode(',', $result->tags),
								explode(',', $result->categories),
								$result->content);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \SpringDvs\Core\NetServices\BulletinManagerInterface::addBulletin()
	 */
	public function addBulletin(\SpringDvs\Core\NetServices\Bulletin $bulletin) {
		$this->db->beginTransaction();
		
		$pid = $this->db->table('nsbulletins')
					->insertGetId([
							'title' => $bulletin->title(),
							'content' => $bulletin->content(),
							'nodeid' => $this->localNode->nodeid()
							//,'created' => time()
					]);

		foreach($bulletin->tags() as $tag) {
			$id = $this->tagId($tag);
			if(!$id){ return null; }
			$this->linkTag($pid, $id);
		}
			
		foreach($bulletin->categories() as $cat) {
			$id = $this->catId($cat);
			if(!$id){ return null; }
			$this->linkCat($pid, $id);
		}

		$this->db->commit();
		return $pid;
	}

	/**
	 * {@inheritDoc}
	 * @see \SpringDvs\Core\NetServices\BulletinManagerInterface::removeBulletin()
	 */
	public function removeBulletin($uid) {
		$this->db->beginTransaction();
		
		$r = $this->db->table('nsbulletins')
						->where([['id', '=', $uid],
								 ['nodeid', '=', $this->localNode->nodeid()]])
						->delete();
						
		if(!$r){ $this->db->commit(); return false; }
			
		$this->db->table('nsbulletin_post_tags')->where('postid', '=', $uid)->delete();
		$this->db->table('nsbulletin_post_cats')->where('postid', '=', $uid)->delete();
		$this->db->commit();
		return true;
	}
	
	/**
	 * Get a tag ID
	 * 
	 * This gets a tag ID from the database. If it does not exist then
	 * a new tag is inserted and that ID is returned
	 * 
	 * @param string $cat The tag to search
	 * @return integer The database ID of the tag
	 */
	private function tagId($tag) {
		$id = $this->db->table('nsbulletin_tags')
				->where([
					['tag', '=', $tag],
					['nodeid', '=', $this->localNode->nodeid()]
				])
				->value('id');
		
		if($id) return $id;
		
		return $this->db->table('nsbulletin_tags')
					->insertGetId(['tag' => $tag, 'nodeid' => $this->localNode->nodeid()]);
	}
	
	/**
	 * Get a category ID
	 * 
	 * This gets a category ID from the database. If it does not exist then
	 * a new category is inserted and that ID is returned
	 * 
	 * @param string $cat The category to search
	 * @return integer The database ID of the category
	 */
	private function catId($cat) {
		$id = $this->db->table('nsbulletin_categories')
				->where([
					['category', '=', $cat],
					['nodeid', '=', $this->localNode->nodeid()]
					])
				->value('id');
	
		if($id) return $id;
	
		return $this->db->table('nsbulletin_categories')
			->insertGetId(['category' => $cat, 'nodeid' => $this->localNode->nodeid()]);
	}
	
	/**
	 * Link a tag to a post
	 * @param integer $postId The post UID
	 * @param integer $tagId The tag UID
	 */
	private function linkTag($postId, $tagId) {
		$this->db->table('nsbulletin_post_tags')
			->insert(['postid' => $postId, 'tagid' => $tagId]);
	}
	
	/**
	 * Link a category to a post
	 * @param integer $postId The post UID
	 * @param integer $catId The category ID
	 */
	private function linkCat($postId, $catId) {
		$this->db->table('nsbulletin_post_cats')
			->insert(['postid' => $postId, 'catid' => $catId]);
	}
	
	/**
	 * Get a list of bulletin headers with no filters, up to limit
	 * 
	 * @param integer $limit The constraint on the number of bulletins 
	 * @return \SpringDvs\Core\NetServices\BulletinHeader[]
	 */
	private function emptyFilters($limit) {
		$bulletins = [];

		// Need to subquery to concat variable number of rows into single field
		// for the final result set
		$q = $this->db->table('nsbulletins as nsb')
					->select('nsb.id',
							 'nsb.title',
							 'nsb.content',
							 $this->db->raw(
								'(SELECT
								  GROUP_CONCAT(DISTINCT nsbt.tag) AS tags
								  FROM nsbulletin_tags As nsbt
								  INNER JOIN nsbulletin_post_tags AS nsbpt
								  ON nsbpt.tagid = nsbt.id
								  WHERE nsbpt.postid = nsb.id
								 ) as tags'
							 ),
							$this->db->raw(
								'(SELECT
								  GROUP_CONCAT(DISTINCT nsbc.category) AS cats
								  FROM nsbulletin_categories As nsbc
								  INNER JOIN nsbulletin_post_cats AS nsbpc
								  ON nsbpc.catid = nsbc.id
								  WHERE nsbpc.postid = nsb.id
								 ) as categories'
									)
							)
					->where('nsb.nodeid', '=', $this->localNode->nodeid())
					->orderBy('nsb.id', 'desc')
					->limit($limit);

		foreach($q->get() as $result) {
			$bulletins[] = new Bulletin(
								$result->id, 
								$result->title,
								explode(',', $result->tags),
								explode(',', $result->categories));
		}
		
		return $bulletins;
	}
	
	private function usingFilters($filters, $limit) {
		$bulletins = [];
		
		// Need to subquery to concat variable number of rows into single field
		// for the final result set
		$q = $this->db->table('nsbulletins as nsb')
		->select('nsb.id',
				'nsb.title',
				'nsb.content',
				$this->db->raw(
						'(SELECT
								  GROUP_CONCAT(DISTINCT nsbt.tag) AS tags
								  FROM nsbulletin_tags As nsbt
								  INNER JOIN nsbulletin_post_tags AS nsbpt
								  ON nsbpt.tagid = nsbt.id
								  WHERE nsbpt.postid = nsb.id
								 ) as tags'
						),
				$this->db->raw(
						'(SELECT
								  GROUP_CONCAT(DISTINCT nsbc.category) AS cats
								  FROM nsbulletin_categories As nsbc
								  INNER JOIN nsbulletin_post_cats AS nsbpc
								  ON nsbpc.catid = nsbc.id
								  WHERE nsbpc.postid = nsb.id
								 ) as categories'
						)
				)
				->orderBy('nsb.id', 'desc')
				->limit($limit);
		
				$tagBranch = isset($filters['tags']) && $filters['tags'] != "";
				$categoryBranch = isset($filters['categories']) && $filters['categories'] != "";
				
									
				if($tagBranch) {
					$tags = explode(',', $filters['tags']);
					$q->join('nsbulletin_post_tags as nsbpt', 'nsbpt.postid', '=', 'nsb.id');
					$q->join('nsbulletin_tags as nsbt', 'nsbt.id', '=', 'nsbpt.tagid');
					
					$wheres = array();
					foreach($tags as $tag) {
						$wheres[] = ['nsbt.tag', '=', trim($tag), 'or'];
					}
					
					$q->where($wheres);
					if(!$categoryBranch) {
						// There is no category branch so we just filter by
						// the node id here
						$q->where('nsbt.nodeid', '=', $this->localNode->nodeid());
					}
				}
				
				if($categoryBranch) {
					$cats = explode(',', $filters['categories']);
					
					if($tagBranch) {
						// If there is a tag branch, we need to filter the postid for the
						// tags join by only those posts that have specified categories, so we 
						// join the categories -> tags -> bulletins, filtering at each level
						$q->join('nsbulletin_post_cats as nsbpc', 'nsbpc.postid', '=', 'nsbpt.postid');
					} else {
						// We just join the category to the bulletin posts, so we are filtering
						// the posts directly using categories
						// join the categegories -> bulletins
						$q->join('nsbulletin_post_cats as nsbpc', 'nsbpc.postid', '=', 'nsb.id');
					}
					$q->join('nsbulletin_categories as nsbc', 'nsbc.id', '=', 'nsbpc.catid');
					$wheres = array();
					foreach($cats as $cat) {
						$wheres[] = ['nsbc.category', '=', trim($cat), 'or'];
					}
					$q->where($wheres);
					$q->where('nsbc.nodeid', '=', $this->localNode->nodeid());
	
				}
				
				// Make doubley sure that the bulletin is made by the node
				$q->where('nsb.nodeid', '=', $this->localNode->nodeid());
		
				foreach($q->get() as $result) {
					$bulletins[] = new Bulletin(
							$result->id,
							$result->title,
							explode(',', $result->tags),
							explode(',', $result->categories));
				}
		
				return $bulletins;
	}
	
}