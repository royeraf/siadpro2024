<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;

/**
 * Ordenamiento por encabezado para los listados paginados.
 *
 * El front-end (BaseTable) envía `sort` (clave de la columna, la misma `key`
 * del arreglo de columnas) y `direction` (`asc`/`desc`). Cada listado define una
 * lista blanca que mapea esa clave a la columna SQL real, de modo que el request
 * nunca puede ordenar por una columna arbitraria.
 */
trait HasSorting
{
    /**
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $query
     * @param  array<string,string>  $columns  clave del front-end => columna SQL
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    protected function applySort(Request $request, $query, array $columns, string $defaultColumn, string $defaultDirection = 'desc')
    {
        $sort = $request->input('sort');
        $direction = strtolower((string) $request->input('direction')) === 'asc' ? 'asc' : 'desc';

        if (is_string($sort) && $sort !== '' && array_key_exists($sort, $columns)) {
            return $query->orderBy($columns[$sort], $direction);
        }

        return $query->orderBy($defaultColumn, $defaultDirection);
    }
}
