<?php

namespace App\Models;

use App\Models\DbModel;
use SpringDvs\Core\NetServices\BulletinManagerServiceInterface;
use SpringDvs\Core\LocalNodeInterface;
use SpringDvs\Core\NetServices\Bulletin;

class ThinWordpressBulletinManager
implements BulletinManagerServiceInterface {
	/**
	 * @var string The base URI to the feed
	 */
	private $feedBase;
	
	/**
	 * @var string The base category for spring bulletins
	 */
	private $categoryBase;
	
	/**
	 * @var LocalNodeInterface The loca node
	 */
	private $localNode;
	
	public function __construct(ThinServices $thinServices, LocalNodeInterface $localNode) {
		
		$config = $thinServices->getConfig('bulletin', $localNode->nodeid());
		$this->feedBase = $config ? $config->feedUri : '';
		$this->categoryBase = $config ? $config->categoryBase : '';
		$this->localNode = $localNode;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \SpringDvs\Core\NetServices\BulletinManagerServiceInterface::withFilters()
	 */
	public function withFilters(array $filters = []) {
		$limit = isset($filters['limit']) ? $filters['limit'] : 5;
		unset($filters['limit']);
		$response = [];
		if(empty($filters)) {
			$response = $this->emptyFilters($limit);
		} else {
			$response = $this->usingFilters($filters, $limit);
		}
		
		return $response;
	}

	/**
	 * {@inheritDoc}
	 * @see \SpringDvs\Core\NetServices\BulletinManagerServiceInterface::withUid()
	 */
	public function withUid($uid, $attributes = []) {
		$url = base64_decode($uid) . '&feed=rss2';
		return $this->feedToBulletin($url, $uid);		
	}
	
	private function emptyFilters($limit) {
		$url = $this->feedBase . '/category/' .$this->categoryBase . '/feed/';
		return $this->feedToHeaders($url, $limit);
	}
	
	private function usingFilters($filters, $limit) {
		$cats = isset($filters['categories']) ? explode(',',$filters['categories']) : [];
		$tags = isset($filters['tags']) ? $filters['tags'] : '';
		$url = $this->feedBase . '/category/' . $this->categoryBase;
		
		if(isset($cats[0])) {
			// Just get the first one for now
			$url .= '/' .  $cats[0] . '-' . $this->categoryBase;
		}
		
		$url .= '/feed/';
		
		if(!empty($tags)) {
			$url .= '?tag='.$tags;
		}
		return $this->feedToHeaders($url, $limit);
	}
		
	private function feedToHeaders($url, $limit) {
		$posts = [];
		try {
			$feed = simplexml_load_file($url);
		} catch(\Exception $e) {
			return $posts;
		}
		
		if(!$feed){
			return $posts;
		}
		
		foreach($feed->channel->item as $post) {
			$posts[] = new Bulletin(base64_encode($post->guid), $post->title, [], []);
			if(--$limit == 0){ break; }
		}
		return $posts;
	}
	
	private function feedToBulletin($url, $uid) {
		try {
			$feed = simplexml_load_file($url);
		} catch(\Exception $e) {
			return null;
		}
		if(!$feed){
			return null;
		}
		
		return new Bulletin($uid, $feed->channel->title, [], [], $feed->channel->link);
	}
}