<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

class UserServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        if (Schema::hasTable('users')) {
            view()->share('cantidadRegistros', User::where('estado', 0)->count());//De aqui se manda los datos
        }
    }
}
