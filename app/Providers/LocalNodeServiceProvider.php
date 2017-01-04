<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\LocalNodeModel;

class LocalNodeServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
		$this->app->singleton(App\Models\LocalNodeModel::class, function($app) {
			$userId = 1;
			return new LocalNodeModel($app->make('db.connection'), $userId);
		});

		$this->app->bind(
				'SpringDvs\Core\LocalNodeInterface',
				App\Models\LocalNodeModel::class
		);
    }
}
