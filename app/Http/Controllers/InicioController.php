<?php

namespace App\Http\Controllers;

use JeroenNoten\LaravelAdminLte\AdminLte;
use JeroenNoten\LaravelAdminLte\Helpers\MenuItemHelper;

class InicioController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        // Sin middleware `can:` a propósito: es el destino de aterrizaje tras el
        // login para todos los roles (ver RouteServiceProvider::HOME), así que
        // no puede exigir un permiso concreto o alguien se quedaría sin poder
        // ni siquiera entrar a la aplicación.
    }

    /**
     * Pantalla de bienvenida con accesos rápidos a los módulos de Actividades.
     * Reutiliza el mismo menú del sidebar (config/adminlte.php) como única
     * fuente de verdad: un módulo marcado con `quick_access` aparece aquí como
     * tarjeta con el mismo icono/color/permiso que ya tiene en el sidebar.
     */
    public function index(AdminLte $adminlte)
    {
        // menu('sidebar') ya pasó por el GateFilter, pero este solo marca los
        // ítems no permitidos con `restricted => true`, no los quita del
        // arreglo — de ahí el filtro explícito con MenuItemHelper::isAllowed().
        $modulos = collect($adminlte->menu('sidebar'))
            ->filter(fn ($item) => ! empty($item['quick_access']) && MenuItemHelper::isAllowed($item))
            ->map(fn ($item) => [
                'text'        => $item['text'],
                'href'        => $item['href'],
                'icon'        => $item['icon'],
                'color'       => $item['icon_color'] ?? null,
                'description' => $item['quick_access'],
            ])
            ->values()
            ->all();

        return view('inicio.index', compact('modulos'));
    }
}
