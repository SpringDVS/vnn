<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use SpringDvs\Core\NetServices\BulletinRepository as BulletinRepository;

class BulletinRepositoryProvider extends ServiceProvider
{

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
		$this->app->bind('App\Providers\BulletinRepositoryModel', function($app){
			return new BulletinRepositoryModel($app->make('db.connection'), $app->make('App\Models\LocalNodeModel'));
		});
		
		$this->app->bind(
			'SpringDvs\Core\NetServices\BulletinRepositoryInterface',
			'App\Models\BulletinRepositoryModel'
		);
    }
}
