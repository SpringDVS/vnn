<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\VnnServerModel;
use App\Models\LocalNodeLookup;
use App\Models\ThinServices;
use SpringDvs\Core\NetServiceHandler;
use App\Models\ThinWordpressBulletinManager;
use SpringDvs\Core\NetServices\Impl\CciBulletinService;
use SpringDvs\Core\NetServiceInterface;
use App\Models\NetServiceViewLoader;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
    	$this->app->singleton(\App\Models\VnnServerModel::class, function($app){
    		return new VnnServerModel();
    	});
    	
    		$this->app->singleton(\App\Models\LocalNodeLookup::class, function($app){
    			return new LocalNodeLookup($app->make('db.connection'),
    									   $app->make(\App\Models\VnnServerModel::class));
    		});
    		
    		$this->app->singleton(\App\Models\ThinServices::class, function($app){
    			return new ThinServices($app->make('db.connection'));
    		});
    		
    		$this->app->singleton(\SpringDvs\Core\NetServiceHandler::class, function($app){
    			return new NetServiceHandler();
    		});
    		
    		$this->app->singleton(\SpringDvs\Core\NetServiceRouter::class,
    			\SpringDvs\Core\NetServiceHandler::class);
    		
    		$this->app->singleton(\SpringDvs\Core\NetServiceViewLoaderInterface::class, function($app) {
    			return new NetServiceViewLoader($app->make(\Illuminate\View\Factory::class));
    		});
    }
}
