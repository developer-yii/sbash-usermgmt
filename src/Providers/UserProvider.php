<?php

namespace Sbash\Usermgmt\Providers;

use Illuminate\Support\ServiceProvider;

class UserProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../views', 'usermgmt');

        $this->publishes([
	        __DIR__.'/../views' => resource_path('views'),
	    ]);
    }
}