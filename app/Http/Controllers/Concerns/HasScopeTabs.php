<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Support\Facades\Auth;

/**
 * Arma las pestañas de una sección fusionada (Mis registros / UGEL / General /
 * Director), mostrando solo las que el usuario autenticado tiene permiso de
 * ver. Es puramente de presentación: el control de acceso real lo sigue
 * imponiendo el middleware `can:` de cada ruta — esto solo evita mostrar un
 * enlace a algo que la ruta rechazaría de todos modos.
 */
trait HasScopeTabs
{
    /**
     * @param array<string, array{permission: string, label: string, route: string}> $items
     *        Clave = identificador de pestaña (para comparar con $activo).
     */
    private function scopeTabs(array $items, string $activo): array
    {
        $user = Auth::user();
        $tabs = [];

        foreach ($items as $key => $item) {
            if ($user->can($item['permission'])) {
                $tabs[] = [
                    'label' => $item['label'],
                    'url' => route($item['route']),
                    // Identidad estable de la pestaña: la URL no sirve porque
                    // arrastra los filtros activos.
                    'route' => $item['route'],
                    'active' => $activo === $key,
                ];
            }
        }

        return $tabs;
    }

    /**
     * Devuelve la clave (index/ugel/general/director…) de la primera pestaña
     * a la que el usuario autenticado tiene acceso, o null si ninguna aplica.
     *
     * @param array<string, array{permission: string, label: string, route: string}> $items
     */
    private function firstAllowedKey(array $items): ?string
    {
        $user = Auth::user();

        foreach ($items as $key => $item) {
            if ($user->can($item['permission'])) {
                return $key;
            }
        }

        return null;
    }
}
