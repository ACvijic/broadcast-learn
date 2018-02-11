<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider,
    Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     *  Bootstraps any application service
     *
     *  @return void
     */
   public function boot()
   {
     Schema::defaultStringLength(191);
   }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
