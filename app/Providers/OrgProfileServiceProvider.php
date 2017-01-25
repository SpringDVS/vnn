<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\OrgProfileManager;
use SpringDvs\Core\NetServices\OrgProfileManagerInterface;
use SpringDvs\Core\NetServices\Impl\CciOrgProfileService;
use SpringDvs\Core\NetServiceHandler;

class OrgProfileServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
    	/**
    	 * @var NetServiceHandler $ns
    	 */
    	$ns = $this->app->make(NetServiceHandler::class);
    	$app = $this->app;
    	
    	$ns->register('orgprofile', function($uriPath, $uriQuery, $localNode) use($app) {
    		
    		$params = ['localNode' => $localNode];
    	
    		/**
    		 * @var NetServiceInterface $service
    		 */
    		$service = $app->make(CciOrgProfileService::class, $params);
    	
    		return $service->run($uriPath, $uriQuery);
    	});
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
		$this->app->bind(OrgProfileManagerInterface::class, function($app, $params){
			return new OrgProfileManager($app->make('db.connection'), $params['localNode']);
		});
		
		$this->app->bind(CciOrgProfileService::class, function($app, $params) {
			return new CciOrgProfileService($this->app->make(OrgProfileManagerInterface::class, $params),
											$params['localNode']);
		});
    }
}
