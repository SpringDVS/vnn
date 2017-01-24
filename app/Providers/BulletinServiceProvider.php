<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\BulletinManagerModel;
use SpringDvs\Core\NetServices\Impl\CciBulletinService;
use App\Models\ThinWordpressBulletinManager;
use App\Models\ThinServices;

class BulletinServiceProvider extends ServiceProvider
{

    /**
     * Register the Bulletin network service.
     *
     * @return void
     */
    public function register()
    {
		$this->app->bind(\App\Models\BulletinManagerModel::class, function($app){
			return new BulletinManagerModel($app->make('db.connection'), $app->make(App\Models\LocalNodeModel::class));
		});
		
		$this->app->bind(
			\SpringDvs\Core\NetServices\BulletinManagerInterface::class,
			\App\Models\BulletinManagerModel::class
		);

		$this->app->bind(
			\SpringDvs\Core\NetServices\BulletinManagerServiceInterfaceInterface::class,
			\App\Models\BulletinManagerModel::class
		);
		
		$this->app->bind(\SpringDvs\Core\NetServices\Impl\CciBulletinService::class, function($app) {
			return new CciBulletinService($app->make(SpringDvs\Core\NetServices\BulletinManagerServiceInterface::class),
										  $app->make(SpringDvs\Core\LocalNodeInterface::class));
		});

		$this->app->bind(\App\Models\ThinWordpressBulletinManager::class, function($app){
			return new ThinWordpressBulletinManager($app->make(ThinServices::class), $app->make(App\Models\LocalNodeModel::class));
		});
			
    }
}
