<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\User;


class UserServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $cantidadRegistros = User::where('estado',0)->count(); // Reemplaza 'TuModelo' con el nombre de tu modelo
        view()->share('cantidadRegistros', User::where('estado',0)->count());
        //config(['adminlte.menu_items.total_registros' => $cantidadRegistros]);
        
    }
}
