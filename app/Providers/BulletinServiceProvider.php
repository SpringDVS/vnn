<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\BulletinManagerModel;
use SpringDvs\Core\NetServices\Impl\CciBulletinService;
use App\Models\ThinWordpressBulletinManager;
use App\Models\ThinServices;
use SpringDvs\Core\NetServiceHandler;
use SpringDvs\Core\NetServiceViewLoaderInterface;

class BulletinServiceProvider extends ServiceProvider
{

	public function boot() {
		/**
		 * @var NetServiceHandler $ns
		 */
		$ns = $this->app->make(NetServiceHandler::class);
		$app = $this->app;
		
		$ns->register('bulletin.wordpress', function($uriPath, $uriQuery, $localNode) use($app) {
			$params = [
					'manager' => ThinWordpressBulletinManager::class,
					'localNode' => $localNode,
					'source' => 'web',
			];
		
			/**
			 * @var NetServiceInterface $service
			 */
			$service = $app->make(CciBulletinService::class, $params);
		
			return $service->run($uriPath, $uriQuery);
		});
	}
    /**
     * Register the Bulletin network service.
     *
     * @return void
     */
    public function register()
    {
		$this->app->bind(\App\Models\BulletinManagerModel::class, function($app, $params){
			return new BulletinManagerModel($app->make('db.connection'), $params['localNode']);
		});
		
		$this->app->bind(
			\SpringDvs\Core\NetServices\BulletinManagerInterface::class,
			\App\Models\BulletinManagerModel::class
		);

		$this->app->bind(
			\SpringDvs\Core\NetServices\BulletinManagerServiceInterfaceInterface::class,
			\App\Models\BulletinManagerModel::class
		);
		
		$this->app->bind(\SpringDvs\Core\NetServices\Impl\CciBulletinService::class, function($app, $params) {
			$source = isset($params['source']) ? $params['source'] : 'spring';
			
			return new CciBulletinService($this->app->make($params['manager'],$params),
										  $this->app->make(NetServiceViewLoaderInterface::class),
										  $params['localNode'], $source);
		});

		$this->app->bind(\App\Models\ThinWordpressBulletinManager::class, function($app, $params){
			return new ThinWordpressBulletinManager($app->make(ThinServices::class), $params['localNode']);
		});			
    }
}
