<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class ConfigServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    { echo "in"; exit;
        $currency = 'whatever you want';
		config([
        'currency' => $currency
		]);
		return  [
    'currency' => '$'
];
    }
}
