<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Métricas del dashboard de `/inicio`.
 *
 * Un único punto de cálculo para todos los perfiles. El alcance ("scope") se
 * deriva del rol y de los datos del usuario, y a partir de ahí se construyen
 * dos vistas complementarias:
 *
 *  - `resumenModulos()`  participación por módulo de actividades (DOCENTE,
 *                        PC, PEC, EspecUGEL, EspecDRE y Admin).
 *  - `jerarquia()`        resumen por territorio o jerarquía: por UGEL para
 *                        DRE/Admin, por institución para UGEL y por docente
 *                        para Director. Es `null` para quien no supervisa un
 *                        nivel intermedio.
 *
 * Todos los conteos se resuelven en BD con agregaciones `GROUP BY`: una
 * consulta por módulo, nunca una por fila. El corpus real ronda los 3.700
 * usuarios, así que traer registros a PHP no es una opción.
 */
class DashboardService
{
    /** Cargos que cuentan como docente para el cálculo de participación. */
    public const DOCENTES_CARGOS = ['Docente', 'Director', 'Profesor Coordinador'];

    /** Módulos de actividades y su tabla de origen. */
    public const MODULOS = [
        ['key' => 'agenda',     'nombre' => 'Agenda de Lectura',                'tabla' => 'pro_agendas'],
        ['key' => 'accion',     'nombre' => 'Acciones de Sensibilización',       'tabla' => 'pro_accions'],
        ['key' => 'difusion',   'nombre' => 'Acciones de Difusión',              'tabla' => 'pro_difusions'],
        ['key' => 'evidencia',  'nombre' => 'Evidencias de Asistencia Técnica', 'tabla' => 'pro_evidencias'],
        ['key' => 'plan',       'nombre' => 'Espacio de Lectura en el Hogar',    'tabla' => 'pro_plans'],
        ['key' => 'produccion', 'nombre' => 'Producción de Textos Infantiles',   'tabla' => 'pro_produccions'],
        ['key' => 'informe',    'nombre' => 'Biblioteca del Aula (Informes)',     'tabla' => 'pro_informes'],
    ];

    /**
     * Columnas candidatas para "fecha de la actividad", en orden de preferencia.
     * `pro_agendas` no usa `fecha`: su fecha de evento vive en `start`.
     */
    private const COLUMNAS_FECHA = ['fecha', 'start', 'created_at'];

    /**
     * Tope de filas del resumen jerárquico. Cubre con holgura los tres niveles
     * reales: 13 UGELs, ~150 instituciones por UGEL y las escuelas de una
     * institución. Si alguna vez se supera, la respuesta marca `truncado`.
     */
    private const MAX_FILAS_JERARQUIA = 200;

    /**
     * Nivel de supervisión del usuario: define hasta dónde se agregan los datos.
     *
     * @return array{scope: string, label: string, userId: ?int}
     */
    public function scope(User $user): array
    {
        $especUGEL = $user->hasRole('EspecUGEL') && ! $this->hasAnyRole($user, ['Admin', 'EspecDRE']);
        $especDRE  = $user->hasRole('EspecDRE') && ! $user->hasRole('Admin');
        $director  = $user->hasRole('Director') && ! $this->hasAnyRole($user, ['Admin', 'EspecDRE', 'EspecUGEL']);

        if ($especUGEL) {
            return ['scope' => 'ugel', 'label' => 'UGEL: ' . $user->ugel, 'userId' => null];
        }

        if ($director) {
            return ['scope' => 'institucion', 'label' => 'Institución: ' . $user->institucion, 'userId' => null];
        }

        // Admin y EspecDRE ven la región completa.
        if ($especDRE || $user->hasRole('Admin')) {
            return ['scope' => 'DRE', 'label' => 'DRE', 'userId' => null];
        }

        // Docente, PC, PEC o cualquier rol sin supervisión: solo lo propio.
        return ['scope' => 'propio', 'label' => 'Propio', 'userId' => $user->id];
    }

    /** El usuario tiene al menos uno de los roles indicados. */
    private function hasAnyRole(User $user, array $roles): bool
    {
        return $roles !== [] && $user->hasAnyRole($roles);
    }

    /** El usuario supervisa un nivel intermedio (región, UGEL o institución). */
    public function tieneJerarquia(User $user): bool
    {
        return in_array($this->scope($user)['scope'], ['DRE', 'ugel', 'institucion'], true);
    }

    /**
     * Participación por módulo. La estructura es idéntica a la que ya consume
     * `ParticipationBar.vue`, para no romper el endpoint existente.
     */
    public function resumenModulos(User $user): array
    {
        try {
            $scope = $this->scope($user);

            $totalDocentes = $this->docentesDelScope($user, $scope);

            $modulos = array_map(function (array $modulo) use ($user, $scope, $totalDocentes) {
                $base = $this->registrosQuery($modulo['tabla'], $user, $scope);

                $registros  = (clone $base)->count();
                $conRegistro = (clone $base)->distinct('users.id')->count('users.id');

                return [
                    'nombre'                => $modulo['nombre'],
                    'key'                   => $modulo['key'],
                    'registros'             => $registros,
                    'docentes_con_registro' => $conRegistro,
                    'porcentaje'            => $this->porcentaje($conRegistro, $totalDocentes),
                ];
            }, self::MODULOS);

            return [
                'scope'         => $scope['label'],
                'totalDocentes' => $totalDocentes,
                'modulos'       => $modulos,
            ];
        } catch (\Throwable $e) {
            Log::warning('DashboardService::resumenModulos() falló', ['error' => $e->getMessage()]);

            return ['scope' => null, 'totalDocentes' => 0, 'modulos' => [], 'error' => true];
        }
    }

    /**
     * Resumen por territorio o jerarquía.
     *
     *  - DRE/Admin  → una fila por UGEL: docentes, registros y % con registro.
     *  - EspecUGEL  → una fila por institución dentro de su UGEL.
     *  - Director   → una fila por docente de su institución, con su última
     *                  actividad y en cuántos módulos tiene registros.
     *
     * @return array<string, mixed>|null  `null` si el perfil no supervisa a nadie.
     */
    public function jerarquia(User $user): ?array
    {
        $scope = $this->scope($user);

        try {
            return match ($scope['scope']) {
                'DRE'         => $this->envoltura($this->resumenPorGrupo('ugel', 'UGEL')),
                'ugel'        => $this->envoltura($this->resumenPorGrupo('institucion', 'Institución', ['ugel' => $user->ugel])),
                'institucion' => $this->jerarquiaPorDocente($user),
                default       => null,
            };
        } catch (\Throwable $e) {
            Log::warning('DashboardService::jerarquia() falló', ['error' => $e->getMessage()]);

            return null;
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    // Niveles territoriales (UGEL / institución)
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Agrega docentes, registros y docentes con registro por una columna de
     * `users`.
     *
     * @param  array<string, mixed>  $filtros  restricciones sobre `users`.
     * @return array<string, mixed>
     */
    private function resumenPorGrupo(string $campo, string $prefijo, array $filtros = []): array
    {
        $vacio = [
            'title'           => "Resumen por {$prefijo}",
            'dimension'       => $prefijo,
            'nivel'           => $prefijo,
            'porcentaje_tipo' => 'participacion',
            'total_docentes'  => 0,
            'registros'       => 0,
            'filas'           => [],
        ];

        $expresion = $this->expresionGrupo($campo);

        if ($expresion === null) {
            return $vacio;
        }

        $docentesPorGrupo = $this->docentesPor($expresion, $filtros);
        $registrosPorGrupo = $this->registrosPorGrupo($expresion, $filtros);

        $grupos = $docentesPorGrupo->keys()
            ->merge($registrosPorGrupo->keys())
            ->unique()
            ->sort()
            ->values();

        $filas = $grupos->map(function ($grupo) use ($docentesPorGrupo, $registrosPorGrupo, $prefijo) {
            $docentes = (int) ($docentesPorGrupo[$grupo] ?? 0);
            $datos    = $registrosPorGrupo[$grupo] ?? ['registros' => 0, 'con_registro' => 0];

            return $this->fila([
                'label'        => $this->nombreGrupo($grupo, $prefijo),
                'docentes'     => $docentes,
                'registros'    => (int) $datos['registros'],
                'con_registro' => (int) $datos['con_registro'],
                'porcentaje'   => $this->porcentaje((int) $datos['con_registro'], $docentes),
            ]);
        })->all();

        return [
            'title'           => "Resumen por {$prefijo}",
            'dimension'       => $prefijo,
            'nivel'           => $prefijo,
            'porcentaje_tipo' => 'participacion',
            'total_docentes'  => (int) $docentesPorGrupo->sum(),
            'registros'       => (int) collect($filas)->sum('registros'),
            'filas'           => $filas,
        ];
    }

    /**
     * Expresión SQL con la que se agrupa (y se selecciona) una columna de `users`.
     *
     * Hace falta normalizar porque el corpus real mezcla mayúsculas y minúsculas
     * ("UGEL HUACAYBAMBA" y "Ugel Huacaybamba"). Con la collation por defecto de
     * MySQL, case-insensitive, un `GROUP BY users.ugel` sobre esas dos formas
     * devuelve un representante *no determinista*: la misma UGEL podía aparecer
     * como dos filas distintas según la consulta. Agrupar por la misma expresión
     * normalizada en SELECT y GROUP BY hace la clave estable.
     *
     * Los filtros WHERE siguen usando la columna cruda: es la búsqueda exacta la
     * que el usuario ya tiene guardada en su perfil.
     */
    private function expresionGrupo(string $campo): ?string
    {
        return Schema::hasColumn('users', $campo) ? "UPPER(TRIM(users.$campo))" : null;
    }

    /** Docentes (cargos válidos, estado 1) agrupados por una expresión. */
    private function docentesPor(string $expresion, array $filtros = []): Collection
    {
        return $this->docentesQuery(['scope' => 'todo', 'userId' => null], $filtros)
            ->selectRaw("$expresion as grupo")
            ->selectRaw('COUNT(DISTINCT users.id) as total')
            ->groupByRaw($expresion)
            ->pluck('total', 'grupo');
    }

    /**
     * Por grupo: cuántos registros aportan sus docentes y cuántos de esos
     * docentes tienen al menos uno.
     *
     * Los dos se calculan por separado a propósito: los registros se suman por
     * módulo, pero el conteo de docentes debe ser el *distinct* de quienes
     * tienen al menos un registro en **cualquier** módulo. Sumar los distinct
     * por módulo contaría varias veces a quien registró en varios módulos.
     */
    private function registrosPorGrupo(string $columna, array $filtros = []): Collection
    {
        $registros = $this->registrosTotalesPorGrupo($columna, $filtros);
        $conRegistro = $this->docentesConRegistroPorGrupo($columna, $filtros);

        $grupos = $registros->keys()->merge($conRegistro->keys())->unique();

        return $grupos->mapWithKeys(fn ($grupo) => [
            $grupo => [
                'registros'    => (int) ($registros[$grupo] ?? 0),
                'con_registro' => (int) ($conRegistro[$grupo] ?? 0),
            ],
        ]);
    }

    /** Registros totales aportados por cada grupo (suma de los 7 módulos). */
    private function registrosTotalesPorGrupo(string $expresion, array $filtros = []): Collection
    {
        $acumulado = [];

        foreach (self::MODULOS as $modulo) {
            $this->registrosQuery($modulo['tabla'], null, ['scope' => 'todo', 'userId' => null], $filtros)
                ->selectRaw("$expresion as grupo")
                ->selectRaw('COUNT(*) as registros')
                ->groupByRaw($expresion)
                ->get()
                ->each(function ($fila) use (&$acumulado) {
                    $grupo = $fila->grupo;

                    $acumulado[$grupo] = ($acumulado[$grupo] ?? 0) + (int) $fila->registros;
                });
        }

        return collect($acumulado);
    }

    /**
     * Docentes distintos con al menos un registro en cualquier módulo.
     *
     * Se resuelve con un `EXISTS` sobre las tablas de los módulos en vez de
     * con un `UNION`: mantiene el conteo en una sola fila por docente y evita
     * arrastrar decenas de miles de filas al servidor de solo para contarlas.
     */
    private function docentesConRegistroPorGrupo(string $expresion, array $filtros = []): Collection
    {
        $query = $this->docentesQuery(['scope' => 'todo', 'userId' => null], $filtros)
            ->selectRaw("$expresion as grupo")
            ->selectRaw('COUNT(DISTINCT users.id) as total')
            ->groupByRaw($expresion);

        // Un docente cuenta si tiene al menos un registro en algún módulo.
        $this->whereTieneRegistro($query);

        return $query->get()->mapWithKeys(fn ($fila) => [$fila->grupo => (int) $fila->total]);
    }

    /**
     * Normaliza una fecha a `Y-m-d`.
     *
     * El corpus mezcla formatos porque cada módulo guarda su fecha en una columna
     * distinta: `fecha` es DATE, `pro_agendas.start` es varchar con ISO 8601 y
     * `created_at` es datetime. Se resuelve aquí para que el frontend solo tenga
     * que pintar `dd/mm/aaaa`.
     */
    private function normalizarFecha(string $valor): ?string
    {
        $valor = trim($valor);

        if ($valor === '' || str_starts_with($valor, '0000-00-00')) {
            return null;
        }

        try {
            return Carbon::parse($valor)->format('Y-m-d');
        } catch (\Throwable $e) {
            Log::debug('DashboardService: fecha no interpretable', ['valor' => $valor]);

            return null;
        }
    }

    /**
     * Primera columna existente que representa la fecha de la actividad, o `null`
     * si la tabla no tiene ninguna.
     */
    private function columnaFecha(string $tabla): ?string
    {
        static $cache = [];

        return $cache[$tabla] ??= (function () use ($tabla) {
            foreach (self::COLUMNAS_FECHA as $columna) {
                if (Schema::hasColumn($tabla, $columna)) {
                    return "$tabla.$columna";
                }
            }

            return null;
        })();
    }

    /**
     * Acota una consulta de `users` a quienes tienen registro en algún módulo.
     *
     * Todo el `OR` va dentro de un paréntesis: sin él, MySQL aplicaría la
     * precedencia `estado=1 AND cargo IN (...) OR EXISTS (...)` y contaría
     * también a usuarios que no son docentes o no están en el alcance.
     */
    private function whereTieneRegistro(EloquentBuilder $query): EloquentBuilder
    {
        $condiciones = array_map(
            fn (array $modulo) => "EXISTS (SELECT 1 FROM {$modulo['tabla']}"
                . " WHERE {$modulo['tabla']}.idUser = users.id"
                . " AND {$modulo['tabla']}.estado = '1')",
            self::MODULOS
        );

        return $query->whereRaw('(' . implode(' OR ', $condiciones) . ')');
    }

    // ─────────────────────────────────────────────────────────────────────
    // Nivel por docente
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Una fila por docente de la institución, con última actividad y cobertura.
     *
     * @return array<string, mixed>
     */
    private function jerarquiaPorDocente(User $user): array
    {
        $totalModulos = count(self::MODULOS);

        $docentes = $this->docentesQuery(
            ['scope' => 'todo', 'userId' => null],
            ['institucion' => $user->institucion]
        )
            ->select('users.id', 'users.name', 'users.cargo')
            ->groupBy('users.id', 'users.name', 'users.cargo')
            ->orderBy('users.name')
            ->limit(self::MAX_FILAS_JERARQUIA)
            ->get();

        $ids = $docentes->pluck('id')->all();

        // Una consulta por módulo, en vez de una por docente (que serían cientos).
        $porUsuario = $this->registrosPorUsuario($ids, ['institucion' => $user->institucion]);

        $filas = $docentes->map(function (User $docente) use ($porUsuario, $totalModulos) {
            $datos = $porUsuario[$docente->id] ?? ['registros' => 0, 'modulos' => 0, 'ultima' => null];

            // Aquí el porcentaje no es "participación" sino cobertura: en cuántos
            // de los módulos el docente tiene al menos un registro.
            return $this->fila([
                'label'           => $docente->name,
                'sublabel'        => $docente->cargo,
                'id'              => (int) $docente->id,
                'docentes'        => 1,
                'registros'       => $datos['registros'],
                'con_registro'    => $datos['registros'] > 0 ? 1 : 0,
                'modulos'         => $datos['modulos'],
                'modulos_totales' => $totalModulos,
                'porcentaje'      => $this->porcentaje($datos['modulos'], $totalModulos),
                'ultima'          => $datos['ultima'],
            ]);
        })->all();

        return $this->envoltura([
            'title'           => 'Resumen por Docente',
            'dimension'       => 'Docente',
            'nivel'           => 'Docente',
            'porcentaje_tipo' => 'cobertura',
            'total_docentes'  => $docentes->count(),
            'registros'       => (int) collect($filas)->sum('registros'),
            'filas'           => $filas,
        ]);
    }

    /**
     * Totales por docente: registros, en cuántos módulos tiene algo y la fecha
     * del registro más reciente.
     *
     * @param  array<int, int>  $ids
     * @param  array<string, mixed>  $filtros
     * @return array<int, array{registros: int, modulos: int, ultima: ?string}>
     */
    private function registrosPorUsuario(array $ids, array $filtros = []): array
    {
        if ($ids === []) {
            return [];
        }

        $porUsuario = [];

        foreach (self::MODULOS as $modulo) {
            $columnaFecha = $this->columnaFecha($modulo['tabla']);

            $query = $this->registrosQuery($modulo['tabla'], null, ['scope' => 'todo', 'userId' => null], $filtros)
                ->whereIn('users.id', $ids)
                ->selectRaw('users.id as usuario_id')
                ->selectRaw('COUNT(*) as total')
                ->selectRaw($columnaFecha ? "MAX($columnaFecha) as ultima" : 'NULL as ultima')
                ->groupBy('users.id');

            foreach ($query->get() as $fila) {
                $id = (int) $fila->usuario_id;

                $porUsuario[$id] ??= ['registros' => 0, 'modulos' => 0, 'ultima' => null];

                $porUsuario[$id]['registros'] += (int) $fila->total;
                $porUsuario[$id]['modulos']    += 1;

                if (! empty($fila->ultima)) {
                    $ultima = $this->normalizarFecha((string) $fila->ultima);

                    if ($ultima !== null && ($porUsuario[$id]['ultima'] === null || $ultima > $porUsuario[$id]['ultima'])) {
                        $porUsuario[$id]['ultima'] = $ultima;
                    }
                }
            }
        }

        return $porUsuario;
    }

    // ─────────────────────────────────────────────────────────────────────
    // Consultas base y utilidades
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Consulta de registros de un módulo, acotada al alcance del usuario.
     *
     * @param  array<string, mixed>  $scope
     * @param  array<string, mixed>  $filtros
     */
    private function registrosQuery(string $tabla, ?User $user, array $scope, array $filtros = []): Builder
    {
        $query = DB::table($tabla)
            ->join('users', 'users.id', '=', "$tabla.idUser")
            ->where("$tabla.estado", '1')
            ->where('users.estado', '1')
            ->whereIn('users.cargo', self::DOCENTES_CARGOS);

        if ($scope['scope'] === 'propio' && $user !== null) {
            $query->where("$tabla.idUser", $user->id);
        } elseif ($scope['scope'] === 'ugel' && $user !== null) {
            $query->where('users.ugel', $user->ugel);
        } elseif ($scope['scope'] === 'institucion' && $user !== null) {
            $query->where('users.institucion', $user->institucion);
        }

        foreach ($filtros as $campo => $valor) {
            if ($valor !== null && $valor !== '') {
                $query->where("users.$campo", $valor);
            }
        }

        return $query;
    }

    /** Usuarios que cuentan como docente dentro del alcance del usuario. */
    private function docentesDelScope(User $user, array $scope): int
    {
        return $this->docentesQuery($scope, $this->filtrosDelScope($user))->distinct('users.id')->count('users.id');
    }

    /**
     * Docentes con cargo válido y estado 1, acotados por alcance y filtros.
     *
     * @param  array<string, mixed>  $scope
     * @param  array<string, mixed>  $filtros
     */
    private function docentesQuery(array $scope, array $filtros = []): EloquentBuilder
    {
        $query = User::query()
            ->from('users')
            ->where('users.estado', '1')
            ->whereIn('users.cargo', self::DOCENTES_CARGOS);

        if ($scope['scope'] === 'propio') {
            $query->where('users.id', $scope['userId']);
        }

        foreach ($filtros as $campo => $valor) {
            if ($valor !== null && $valor !== '') {
                $query->where("users.$campo", $valor);
            }
        }

        return $query;
    }

    /** Traduce el alcance del usuario a filtros de `users`. */
    private function filtrosDelScope(User $user): array
    {
        return match ($this->scope($user)['scope']) {
            'ugel'        => ['ugel' => $user->ugel],
            'institucion' => ['institucion' => $user->institucion],
            'propio'      => [],
            default       => [],
        };
    }

    /**
     * Normaliza el resultado jerárquico: agrega los totales, fija el orden por
     * defecto y marca si se truncó.
     *
     * Los totales se calculan sobre el conjunto completo de filas (antes de
     * truncar) para que el porcentaje global no dependa del tope.
     */
    private function envoltura(array $payload): array
    {
        $todas = collect($payload['filas']);

        $conRegistro = (int) $todas->sum('con_registro');

        $filas = $todas
            ->sortByDesc('porcentaje')
            ->take(self::MAX_FILAS_JERARQUIA)
            ->values()
            ->all();

        return array_merge($payload, [
            'filas'             => $filas,
            'total_filas'       => $todas->count(),
            'con_registro'      => $conRegistro,
            'porcentaje_global' => $this->porcentaje($conRegistro, (int) $payload['total_docentes']),
            'truncado'          => $todas->count() > count($filas),
        ]);
    }

    /** Normaliza una fila: garantiza todas las claves con un valor por defecto. */
    private function fila(array $fila): array
    {
        return array_merge([
            'label'           => 'Sin nombre',
            'sublabel'        => null,
            'id'              => null,
            'docentes'        => 0,
            'registros'       => 0,
            'con_registro'    => 0,
            'porcentaje'      => 0.0,
            'modulos'         => null,
            'modulos_totales' => null,
            'ultima'          => null,
        ], $fila);
    }

    private function porcentaje(int $parte, int $total): float
    {
        return $total > 0 ? round($parte / $total * 100, 1) : 0.0;
    }

    /**
     * Nombre legible del grupo. Las UGEL vienen en mayúsculas o minúsculas
     * según quién las escribió, así que se normaliza el formato, no el valor.
     */
    private function nombreGrupo(?string $valor, string $prefijo): string
    {
        $valor = trim((string) $valor);

        if ($valor === '') {
            return 'Sin ' . strtolower($prefijo);
        }

        if (strcasecmp($prefijo, 'UGEL') === 0) {
            return 'Ugel ' . ucwords(mb_strtolower(preg_replace('/^ugel\s+/i', '', $valor)));
        }

        return $valor;
    }
}
