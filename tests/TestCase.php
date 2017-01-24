<?php

abstract class TestCase extends Illuminate\Foundation\Testing\TestCase
{
    /**
     * The base URL to use while testing the application.
     *
     * @var string
     */
    protected $baseUrl = 'http://localhost';
    
    /**
     * @var integer The local node ID
     */
	protected $nodeId;
    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
    	putenv('DB_DEFAULT=inmemory');
        $app = require __DIR__.'/../bootstrap/app.php';
        $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
        return $app;
    }
    
    public function setUp() {
    	parent::setUp();
    	Artisan::call('migrate');
    }
    
    public function tearDown() {
    	Artisan::call('migrate:reset');
    	parent::tearDown();
    }
}
