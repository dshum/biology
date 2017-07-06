<?php

namespace Moonlight;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Moonlight\Main\Site;
use Illuminate\Support\Facades\DB;

class MoonlightServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $site = \App::make('site');

		$site->initMicroTime();

		if (file_exists($path = app_path().'/Http/site.php')) {
			include $path;
		}
        
        $this->loadViewsFrom(__DIR__.'/resources/views', 'moonlight');
        
        $this->publishes([
            __DIR__.'/database/migrations' => $this->app->databasePath().'/migrations',
            __DIR__.'/database/seeds' => $this->app->databasePath().'/seeds',
            __DIR__.'/resources/assets/dist' => public_path('moonlight'),
        ]);
        
        DB::enableQueryLog(); 
        
        include __DIR__.'/routes.php';
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        \App::singleton('site', function($app) {
			return new Site;
		}); 
    }
}
