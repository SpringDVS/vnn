<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\BulletinRepositoryModel as BulletinRepositoryModel;

class BulletinRepositoryProvider extends ServiceProvider
{

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
		$this->app->bind(\App\Models\BulletinRepositoryModel::class, function($app){
			return new BulletinRepositoryModel($app->make('db.connection'), $app->make(App\Models\LocalNodeModel::class));
		});
		
		$this->app->bind(
			\SpringDvs\Core\NetServices\BulletinRepositoryInterface::class,
			\App\Models\BulletinRepositoryModel::class
		);
    }
}
