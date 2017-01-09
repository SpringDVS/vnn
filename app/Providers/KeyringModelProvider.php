<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use \App\Models\KeyringModel as KeyringModel;
use SpringDvs;

class KeyringModelProvider extends ServiceProvider
{

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
		$this->app->bind(App\Models\KeyringModel::class, function($app){
			return new KeyringModel($app->make('db.connection'), $app->make(App\Models\LocalNodeModel::class));
		});
		
		$this->app->bind(
			SpringDvs\Core\NetServices\KeyringInterface::class,
			App\Models\KeyringModel::class
		);
    }
}
