<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\CertServiceKeyStore;


class CertServiceProvider extends ServiceProvider
{

    /**
     * Register the Bulletin network service.
     *
     * @return void
     */
    public function register()
    {
		$this->app->bind(\App\Models\CertServiceKeyStore::class, function($app){
			return new CertServiceKeyStore($app->make('db.connection'), $app->make(App\Models\LocalNodeModel::class));
		});
		
		$this->app->bind(\SpringDvs\Core\NetServices\CertKeyStoreInterface::class,
						 \App\Models\CertServiceKeyStore::class);
    
}
