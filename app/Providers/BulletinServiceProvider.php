<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\BulletinManagerModel;

class BulletinServiceProvider extends ServiceProvider
{

    /**
     * Register the application services.
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
    }
}
