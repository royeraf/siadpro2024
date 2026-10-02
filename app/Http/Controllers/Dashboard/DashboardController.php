<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Inertia\Inertia;

/**
 * Dashboard del SPA, en `/dashboard`.
 *
 * Es la única pantalla de métricas y se adapta al perfil de quien entra. El
 * alcance y los conteos los decide `DashboardService` a partir del rol:
 *
 *  - Admin / EspecDRE → resumen por UGEL.
 *  - EspecUGEL        → resumen por institución de su UGEL.
 *  - Director         → resumen por docente (cobertura y última actividad).
 *  - Docente / PC / PEC → `jerarquia` llega `null`: no supervisan a nadie, así
 *    que solo ven la participación por módulo.
 *
 * No sustituye a `InicioController` (`/inicio`), que sigue siendo la pantalla
 * de bienvenida y accesos rápidos.
 *
 * Nota de nombres: el `App\Http\Controllers\DashboardController` de la raíz es
 * el dashboard Blade heredado, que se conserva accesible en `/dashboard-index`
 * sin entrada de menú. Este vive en el sub-namespace `Dashboard\` para no
 * colisionar con él.
 */
class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(DashboardService $dashboard)
    {
        $usuario = auth()->user();

        return Inertia::render('Dashboard/Index', [
            'stats'     => $dashboard->resumenModulos($usuario),
            'jerarquia' => $dashboard->jerarquia($usuario),
        ]);
    }
}
