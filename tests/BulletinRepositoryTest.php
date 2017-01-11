<?php

class BulletinRepositoryTest extends TestCase
{
	/**
	 * @var \SpringDvs\Core\NetServices\BulletinManagerInterface The bulletin repository
	 */
	private $repo;
	
	public function setUp() {
		parent::setUp();
		$netId = DB::table('regionals')->insert(
				['network' => 'venus']
				);
		 
		$this->nodeId = DB::table('nodes')->insert(
				['springname' => 'alpha']
				);
		 
		DB::table('clusters')->insert(
				['netid' => $netId, 'nodeid' => $this->nodeId]
				);
		 
		DB::table('userassoc')->insert(
				['uid' => 1, 'nodeid' => $this->nodeId]
				);
		 
		$this->repo = $this->app->make('SpringDvs\Core\NetServices\BulletinManagerInterface');
		 
		$_SERVER['HTTP_HOST'] = 'section9';
	}
    /**
     * Test adding a bulletin to the database
     *
     * @return void
     */
    public function testAddBulletin()
    {
		$uid = $this->helperAddBulletin();
		
		
		
        // Assert the creation of the bulletin content
		$post = DB::table('nsbulletins')->select(['id','title','content'])->first();
        
		$this->assertNotNull($uid);
        $this->assertEquals('Post Title', $post->title);
        
        $this->assertEquals('Post content', $post->content);
        
        // Assert the tags have been created for node 1
        $tagFoo = DB::table('nsbulletin_tags')->select('id', 'nodeid', 'tag')->where('tag', '=', 'foo')->first();
        $this->assertEquals('foo', $tagFoo->tag);
        $this->assertEquals(1, $tagFoo->nodeid);

        $tagBar = DB::table('nsbulletin_tags')->select('id', 'nodeid', 'tag')->where('tag', '=', 'bar')->first();
        $this->assertEquals('bar', $tagBar->tag);
        $this->assertEquals(1, $tagBar->nodeid);

        // Assert the category has been created for node 1
        $cat = DB::table('nsbulletin_categories')->select('id', 'nodeid', 'category')->where('category', '=', 'Foo')->first();
        $this->assertNotNull($cat);
        $this->assertEquals('Foo', $cat->category);
        $this->assertEquals(1, $cat->nodeid);
        
        // Assert only one category has been created
        $cats = DB::table('nsbulletin_categories')->select('id', 'nodeid', 'category')->where('category', '=', 'Foo')->get();
        $this->assertCount(1, $cats);
        
        // Assert the tags have been linked to the post
        $linkTagFoo = DB::table('nsbulletin_post_tags')
        					->select('tagid')->where([
        							['postid', '=', $post->id],
        							['tagid', '=', $tagFoo->id]
        					])->first();
        $this->assertEquals($tagFoo->id, $linkTagFoo->tagid);
        
        $linkTagBar = DB::table('nsbulletin_post_tags')
					        ->select('tagid')->where([
					        		['postid', '=', $post->id],
					        		['tagid', '=', $tagBar->id]
					        ])->first();
        $this->assertEquals($tagBar->id, $linkTagBar->tagid);

        // Assert the cat has been linked with the post
        $linkTagCat = DB::table('nsbulletin_post_cats')
					        ->select('catid')->where([
					        		['postid', '=', $post->id],
					        		['catid', '=', $cat->id]
					        ])->first();
        $this->assertEquals($cat->id, $linkTagFoo->tagid);
    }
    
    /**
     * Test handling for duplicate tags
     */
    public function testAddBulletinDuplicateTag() {

		$b1 = $this->helperAddBulletin(['foo', 'bar'], ['Cat1']);
		$b2 = $this->helperAddBulletin(['foo'], ['Cat2']);
		
		    	
    	// Assert there is only one tag for 'foo'
    	$tags = DB::table('nsbulletin_tags')->select('id')->where('tag', '=', 'foo')->get();
    	$this->assertCount(1, $tags);
    	
    	$linkedPosts = Db::table('nsbulletin_post_tags')->select('id')->where('tagid','=',$tags[0]->id)->get();
    	$this->assertCount(2, $linkedPosts);
    }
    
    /**
     * Test handling for duplicate categories
     */
    public function testAddBulletinDuplicateCategory() {
   	
		$this->helperAddBulletin(['foo']);
		$this->helperAddBulletin(['bar']);
    					 
    	// Assert there is only one category for 'Foo'
    	$cats = DB::table('nsbulletin_categories')->select('id')->where('category', '=', 'Foo')->get();
    	$this->assertCount(1, $cats);
    	
    	$linkedPosts = Db::table('nsbulletin_post_cats')->select('id')->where('catid','=',$cats[0]->id)->get();
    	$this->assertCount(2, $linkedPosts);
    }
    
    /**
     * Test removal of a bulletin
     */
    public function testDeleteBulletin() {
		$uid = $this->helperAddBulletin();
		$this->assertTrue($this->repo->removeBulletin($uid));
		
		$post = DB::table('nsbulletins')->select('id')->where('id','=',$uid)->get();
		$this->assertCount(0, $post);
		
		$linkedTags = Db::table('nsbulletin_post_tags')->select('id')->where('postid','=',$uid)->get();
		$this->assertCount(0, $linkedTags);

		$linkedCats = Db::table('nsbulletin_post_cats')->select('id')->where('postid','=',$uid)->get();
		$this->assertCount(0, $linkedCats);
    }
    
    /**
     * Test handling for removing bulletin with Bad UID
     */
    public function testDeleteBulletinBadUid() {
		$this->helperAddBulletin();
    	$this->assertFalse($this->repo->removeBulletin(101));
    }
    
    /**
     * Test handling of removing bulletin from different node
     */
    public function testDeleteBulletinBadNodeId() {
    
    	$id = $this->helperAddBulletin();
    	$this->helperChangeNodeId();
    	$this->assertFalse($this->repo->removeBulletin($id));
    }
    
    /**
     * Test getting a bulletin with UID
     */
    public function testGetBulletinWithId() {
    	$tags = ['foo','bar'];
    	$cats = ['Foo'];
    	$uid = $this->helperAddBulletin($tags,$cats);
    	$bulletin = $this->repo->withUid($uid);
    	
    	$this->assertEquals($uid, $bulletin->uid());
    	$this->assertEquals('Post Title', $bulletin->title());
    	$this->assertEquals('Post content', $bulletin->content());
    	$this->assertEquals($tags, $bulletin->tags());
    	$this->assertEquals($cats, $bulletin->categories());
    }
    
    /**
     * Test handling of getting a bulletin with bad UID
     */
    public function testGetBulletinWithBadUid() {
    	$this->helperAddBulletin();
    	$this->assertNull($this->repo->withUid(101));
    }
    
    /**
     * Test handling fo getting bulletin from different node
     */
    public function testGetBulletinBadNodeId() {
    	$uid = $this->helperAddBulletin();
    	$this->helperChangeNodeId();
    	$this->assertNull($this->repo->withUid($uid));
    }

    /**
     * Test retrieving bulletin headers, empty filters, repo size < default limit
     */
    public function testGetBulletinsWithEmptyFiltersWithinDefaultLimit() {
    	$this->helperFillRepo(2,
    			[
    				2 => [
    					'tags' => ['other'],
    					'cats' => ['Misc'],
    				]
    			]);

    	$headers = $this->repo->withFilters();
    	
    	// Should retrieve the two bulletins
    	$this->assertCount(2, $headers);

    	// Should be coming in reverse order (newest first)
    	$this->assertEquals($headers[0]->title(), 'Post Title 2');
    	$this->assertEquals($headers[0]->tags(), ['other']);
    	$this->assertEquals($headers[0]->categories(), ['Misc']);

    	$this->assertEquals($headers[1]->title(), 'Post Title 1');
    	$this->assertEquals($headers[1]->tags(), ['foo','bar']);
    	$this->assertEquals($headers[1]->categories(), ['Foo']);
    }

    /**
     * Test retrieving bulletin headers, empty filters, constrain limit to 2
     */
    public function testGetBulletinsWithEmptyFiltersLimitTwo() {
    	
    	$this->helperFillRepo(3,
		    	[
		    		2 => [
	    				'tags' => ['other'],
	    				'cats' => ['Misc'],
		    		]
		    	]);

    	$headers = $this->repo->withFilters(['limit' => 2]);

    	// Should retrieve two of the three bulletins
    	$this->assertCount(2, $headers);
    	
    	// Should be coming in reverse order (newest first)
    	$this->assertEquals($headers[0]->title(), 'Post Title 3');
    	$this->assertEquals($headers[0]->tags(), ['foo','bar']);
    	$this->assertEquals($headers[0]->categories(), ['Foo']);
    }
    
    /**
     * Test retrieving bulletin headers, empty filters, repo size > default limit 
     */
    public function testGetBulletinsWithEmptyFiltersUpToDefaultLimit() {
		$this->helperFillRepo(7);

    	$headers = $this->repo->withFilters();
   	 
    	// Should retrieve the limit of 5 of the 7 bulletins
    	$this->assertCount(5, $headers);
    
    	// Should be coming in reverse order (newest first)
    	$this->assertEquals('Post Title 7', $headers[0]->title());
    	$this->assertEquals('Post Title 3', $headers[4]->title());
    	
    }

    /**
     * Test retrieving bulletin headers, tag filters, repo size > default limit
     */
    public function testGetBulletinsWithTagFiltersUpToDefaultLimit() {
    	
    	$this->helperFillRepo(7);
    	$headers = $this->repo->withFilters(['tags' => 'foo,other']);
    	 
    	// Should retrieve the limit of 5 of the 7 bulletins
    	$this->assertCount(5, $headers);
    
    	// Should be coming in reverse order (newest first)
    	$this->assertEquals('Post Title 7', $headers[0]->title());
    	$this->assertEquals('Post Title 3', $headers[4]->title());
    	 
    }
    
    /**
     * Test retrieving bulletin headers, single hit tag filter, repo size > default limit
     */
    public function testGetBulletinsWithSingleHitTagFiltersUpToDefaultLimit() {
    	
    	$this->helperFillRepo(7, [
    							3 => ['tags' => ['foo','test'] ],
    						] );
    
    	$headers = $this->repo->withFilters(['tags' => 'other,test']);
    
    	// Should retrieve the limit of 5 of the 7 bulletins
    	$this->assertCount(1, $headers);
    	$this->assertEquals('Post Title 3', $headers[0]->title());
    }

    /**
     * Test retrieving bulletin headers, bad tag filter, repo size > default limit
     */
    public function testGetBulletinsWithBadTagFiltersUpToDefaultLimit() {
    	
    	$this->helperFillRepo(7);
    
    	$headers = $this->repo->withFilters(['tags' => 'other']);
    
    	// Should retrieve the limit of 0 of the 7 bulletins
    	$this->assertCount(0, $headers);    
    }
    
    /**
     * Test retrieving bulletin headers, tag filters, repo size > default limit
     */
    public function testGetBulletinsWithCatFiltersUpToDefaultLimit() {
 		
    	$this->helperFillRepo(7);
    	
    	$headers = $this->repo->withFilters(['cats' => 'Foo,Other']);
    
    	// Should retrieve the limit of 5 of the 7 bulletins
    	$this->assertCount(5, $headers);
    
    	// Should be coming in reverse order (newest first)
    	$this->assertEquals('Post Title 7', $headers[0]->title());
    	$this->assertEquals('Post Title 3', $headers[4]->title());
    
    }
    
    /**
     * Test retrieving bulletin headers, single hit cat filter, repo size > default limit
     */
    public function testGetBulletinsWithSingleHitCatFiltersUpToDefaultLimit() {
    	 
    	$this->helperFillRepo(7, [
    			3 => ['cats' => ['Test','Other'] ],
    	] );
    
    	$headers = $this->repo->withFilters(['categories' => 'Test']);
    
    	// Should retrieve the limit of 5 of the 7 bulletins
    	$this->assertCount(1, $headers);
    	$this->assertEquals('Post Title 3', $headers[0]->title());
    }
    
    /**
     * Test retrieving bulletin headers, Bad cat filter, repo size > default limit
     */
    public function testGetBulletinsWithBadCatFiltersUpToDefaultLimit() {
    	$this->helperFillRepo(7);
    	$headers = $this->repo->withFilters(['categories' => 'Other']);
    
    	// Should retrieve 0 of the 7 bulletins
    	$this->assertCount(0, $headers);
    }
    
    /**
     * Test retrieving bulletin headers, tag and cat filters, repo size > default limit
     */
    public function testGetBulletinsWithTagCatFiltersUpToDefaultLimit() {
    	$this->helperFillRepo(7);
    	$headers = $this->repo->withFilters(['tags' => 'foo', 'categories' => 'Foo']);
    
    	// Should retrieve 5 of the 7 bulletins
    	$this->assertCount(5, $headers);
    	
    	// Should be coming in reverse order (newest first)
    	$this->assertEquals('Post Title 7', $headers[0]->title());
    	$this->assertEquals('Post Title 3', $headers[4]->title());
    }
    
    /**
     * Test retrieving bulletin headers, tag and single hit cat filters, repo size > default limit
     */
    public function testGetBulletinsWithTagSingleHitCatFiltersUpToDefaultLimit() {
    	$this->helperFillRepo(7, [
    			3 => ['cats' => ['Test'] ],
    	] );
    	$headers = $this->repo->withFilters(['tags' => 'foo', 'categories' => 'Test']);
    
    	// Should retrieve 1 of the 7 bulletins
    	$this->assertCount(1, $headers);
    	 
    	// Should be coming in reverse order (newest first)
    	$this->assertEquals('Post Title 3', $headers[0]->title());
    	$this->assertEquals(['Test'], $headers[0]->categories());
    }
    
    /**
     * Test retrieving bulletin headers, single hit tag and cat filters, repo size > default limit
     */
    public function testGetBulletinsWithSingleHitTagCatFiltersUpToDefaultLimit() {
    	$this->helperFillRepo(7, [
    			3 => ['tags' => ['test'] ],
    	] );
    	$headers = $this->repo->withFilters(['tags' => 'test', 'categories' => 'Foo']);
    
    	// Should retrieve 1 of the 7 bulletins
    	$this->assertCount(1, $headers);
    
    	// Should be coming in reverse order (newest first)
    	$this->assertEquals('Post Title 3', $headers[0]->title());
    	$this->assertEquals(['test'], $headers[0]->tags());
    }
    
    /**
     * Test retrieving bulletin headers, bad tag and cat filters, repo size > default limit
     */
    public function testGetBulletinsWithBadTagCatFiltersUpToDefaultLimit() {
    	$this->helperFillRepo(7);
    	$headers = $this->repo->withFilters(['tags' => 'bad', 'categories' => 'Foo']);
    
    	// Should retrieve 0 of the 7 bulletins
    	$this->assertCount(0, $headers);
    }

    /**
     * Test retrieving bulletin headers, tag and bad cat filters, repo size > default limit
     */
    public function testGetBulletinsWithTagBadCatFiltersUpToDefaultLimit() {
    	$this->helperFillRepo(7);
    	$headers = $this->repo->withFilters(['tags' => 'foo', 'categories' => 'Bad']);
    
    	// Should retrieve 0 of the 7 bulletins
    	$this->assertCount(0, $headers);
    }


    /**
     * Test retrieving bulletin headers, mix and match tag and cat filters, repo size > default limit
     */
    public function testGetBulletinsWithMixMatchTagCatFiltersUpToDefaultLimit() {
    	$this->helperFillRepo(7, [
    			2 => ['tags' => ['wonky'] ],
    			3 => ['tags' => ['test'] ],
    	] );
    	$headers = $this->repo->withFilters(['tags' => 'test,wonky', 'categories' => 'Foo']);
    
    	// Should retrieve 2 of the 7 bulletins
    	$this->assertCount(2, $headers);

    	// Should be coming in reverse order (newest first)
    	$this->assertEquals('Post Title 3', $headers[0]->title());
    	$this->assertEquals(['test'], $headers[0]->tags());
    	
    	$this->assertEquals('Post Title 2', $headers[1]->title());
    	$this->assertEquals(['wonky'], $headers[1]->tags());
    }
    
    /**
     * Test retrieving bulletin headers, tag and mixmatch cat filters, repo size > default limit
     */
    public function testGetBulletinsWithTagMixMatchCatFiltersUpToDefaultLimit() {
    	$this->helperFillRepo(7, [
    			2 => ['tags' => ['test'],
    				  'cats' => ['Wonky'] ],

    			3 => ['tags' => ['test'] ],
    	] );
    	$headers = $this->repo->withFilters(['tags' => 'test', 'categories' => 'Foo,Wonky']);
    
    	// Should retrieve 2 of the 7 bulletins
    	$this->assertCount(2, $headers);
    
    	// Should be coming in reverse order (newest first)
    	$this->assertEquals('Post Title 3', $headers[0]->title());
    	$this->assertEquals(['test'], $headers[0]->tags());
    	
    	$this->assertEquals('Post Title 2', $headers[1]->title());
    	$this->assertEquals(['test'], $headers[1]->tags());
    	$this->assertEquals(['Wonky'], $headers[1]->categories());
    }
    
    /**
     * Test retrieving bulletin headers, tag and mixmatch cat filters, repo size > default limit
     */
    public function testGetBulletinsWithTagMixMatchCatFiltersLimitOne() {
    	$this->helperFillRepo(7, [
    			2 => ['tags' => ['test'],
    					'cats' => ['Wonky'] ],
    
    			3 => ['tags' => ['test'] ],
    	] );
    	$headers = $this->repo->withFilters(['tags' => 'test', 'categories' => 'Foo,Wonky', 'limit' => 1]);
    
    	// Should retrieve 1 of the 7 bulletins
    	$this->assertCount(1, $headers);
    
    	// Should be coming in reverse order (newest first)
    	$this->assertEquals('Post Title 3', $headers[0]->title());
    	$this->assertEquals(['test'], $headers[0]->tags());
    }
    
    /**
     * Test retrieving bulletin headers, malformed cat filters, repo size > default limit
     */
    public function testGetBulletinsWithMalformedCatFiltersLimitOne() {
    	$this->helperFillRepo(7);
    	$headers = $this->repo->withFilters(['categories' => ' ']);
    
    	// Should retrieve 0 of the 7 bulletins
    	$this->assertCount(0, $headers);
    }
    
    /**
     * Test retrieving bulletin headers, malformed tag filters, repo size > default limit
     */
    public function testGetBulletinsWithMalformedTagFiltersLimitOne() {
    	$this->helperFillRepo(7);
    	$headers = $this->repo->withFilters(['tags' => ' ']);
    
    	// Should retrieve 0 of the 7 bulletins
    	$this->assertCount(0, $headers);
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
    
    private function helperFillRepo($size, $jokers = []) {
    	for($i = 1; $i <= $size; $i++) {
    		
    		$tags = isset($jokers[$i]) && isset($jokers[$i]['tags'])
    					? $jokers[$i]['tags'] : null;
    		$cats = isset($jokers[$i]) && isset($jokers[$i]['cats'])
    					? $jokers[$i]['cats'] : null;
    		
    		$this->helperAddBulletin($tags,$cats,"Post Title $i");
    	}
    }
    
    private function helperChangeNodeId($uid = 1, $nodeid = 1000) {
    	DB::table('nsbulletins')->where('id','=',$uid)->update(['nodeid' => $nodeid]);
    }
}
