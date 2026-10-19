<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HasScopeTabs;
use App\Models\Agenda;
use App\Models\AgendaEvidencia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class AgendaController extends Controller
{
    use HasScopeTabs;

    /** Tope de días programados por agenda (guarda contra rangos absurdos). */
    private const MAX_DIAS = 60;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:agendas.index')->only('index');
        $this->middleware('can:agendas.create')->only('store');
        $this->middleware('can:agendas.edit')->only('update');
        $this->middleware('can:agendas.destroy')->only('destroy');
        // landing() se queda sin permiso propio: valida por sí misma con
        // abort_if(empty($tabs)) contra las pestañas a las que el usuario
        // realmente tenga acceso (ver landing() más abajo).
    }

    public function index()
    {
        $usuario = Auth::user()->id;

        $events = Agenda::where('idUser', $usuario)
            ->where('estado', '1')
            ->with('dias')
            ->orderBy('id')
            ->get()
            ->map(fn ($e) => [
                'id'      => $e->id,
                'title'   => $e->title,
                'evento'  => $e->evento,
                'color'   => $e->color,
                'seccion' => $e->seccion,
                'start'   => $this->isoDateTime($e->start),
                'end'     => $this->isoDateTime($e->end),
                'dias'    => $e->dias->map(fn ($d) => [
                    'id'          => $d->id,
                    'fecha'       => $d->fecha?->format('Y-m-d'),
                    'hora_inicio' => $d->hora_inicio ? substr($d->hora_inicio, 0, 5) : null,
                    'hora_fin'    => $d->hora_fin ? substr($d->hora_fin, 0, 5) : null,
                ])->values(),
            ]);

        $tabs = $this->tabsAgenda('index');

        return Inertia::render('Agenda/Index', [
            'events'    => $events,
            'tabs'      => $tabs,
            'secciones' => Agenda::SECCIONES,
            'can'       => [
                'create'    => Auth::user()->can('agendas.create'),
                'edit'      => Auth::user()->can('agendas.edit'),
                'destroy'   => Auth::user()->can('agendas.destroy'),
                'evidencia' => Auth::user()->can('agendas.evidencia'),
            ],
        ]);
    }

    /**
     * start/end son varchar y guardan lo que emite <input type="datetime-local">
     * (2026-09-14T00:00). FullCalendar espera ISO 8601, así que se completa con
     * los segundos; también tolera el formato antiguo "YYYY-MM-DD HH:mm:ss".
     */
    private function isoDateTime(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return $value;
        }

        $value = str_replace(' ', 'T', trim($value));

        if (preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $value)) {
            return $value . ':00';
        }

        return $value;
    }

    /**
     * Pestañas de "Agenda de Lectura". agendas.general/agendas.ugel se crean
     * en AgendaPermissionSeeder — antes estaban prestados de evidencias.view
     * y plans.ugel en el menú (copy-paste), sin permiso propio.
     */
    private function tabsAgenda(string $activo): array
    {
        return $this->scopeTabs([
            'index'   => ['permission' => 'agendas.index', 'label' => 'Mis registros', 'route' => 'agendas.index'],
            'view'    => ['permission' => 'agendas.view', 'label' => 'Director', 'route' => 'agendas.view'],
            'ugel'    => ['permission' => 'agendas.ugel', 'label' => 'UGEL', 'route' => 'agenda.ugel'],
            'general' => ['permission' => 'agendas.general', 'label' => 'General', 'route' => 'agenda.general'],
        ], $activo);
    }

    /**
     * Punto de entrada del menú. agendas.index (Admin+Docente) y agendas.view
     * (Admin+Director) no cubren a EspecDRE/EspecUGEL, que solo tienen
     * agendas.general/agendas.ugel — redirige a la primera pestaña accesible.
     */
    public function landing()
    {
        $tabs = $this->tabsAgenda('index');
        abort_if(empty($tabs), 403);

        return redirect($tabs[0]['url']);
    }

    public function store(Request $request)
    {
        $data = $this->validar($request);

        $agenda = new Agenda();
        $agenda->title  = $data['title'];
        $agenda->evento = $data['evento'];
        $agenda->color  = $data['color'] ?? '';
        $agenda->idUser = Auth::id();
        $agenda->estado = 1;

        [$start, $end] = $this->rangoDesdeDias($data['dias']);
        $agenda->start = $start;
        $agenda->end   = $end;
        $agenda->save();

        $this->guardarDias($agenda, $data['dias']);

        return redirect('/agendas')->with('success', '¡Evento registrado con éxito!');
    }

    public function update(Request $request, Agenda $agenda)
    {
        // agendas.edit (exigido por el constructor) no distingue de quién es el
        // registro: sin esto, cualquier usuario con permiso de escritura podía
        // modificar la agenda de cualquier otro, con solo conocer el id.
        abort_unless((int) $agenda->idUser === Auth::id(), 403);

        $data = $this->validar($request);

        $agenda->title  = $data['title'];
        $agenda->evento = $data['evento'];
        $agenda->color  = $data['color'] ?? '';

        [$start, $end] = $this->rangoDesdeDias($data['dias']);
        $agenda->start = $start;
        $agenda->end   = $end;
        $agenda->save();

        $this->guardarDias($agenda, $data['dias']);

        return redirect('/agendas')->with('success', '¡Evento actualizado con éxito!');
    }

    public function destroy(Agenda $agenda)
    {
        abort_unless((int) $agenda->idUser === Auth::id(), 403);

        $agenda->estado = 0;
        $agenda->save();

        return redirect('/agendas')->with('success', '¡Evento eliminado con éxito!');
    }

    /**
     * Valida el payload de crear/editar (JSON) y devuelve los datos limpios.
     */
    private function validar(Request $request): array
    {
        $data = $request->validate([
            'title'              => 'required|string|max:191',
            'evento'             => 'required|string|max:191',
            'color'              => 'nullable|string|max:50',
            'dias'               => 'required|array|min:1|max:' . self::MAX_DIAS,
            'dias.*.fecha'       => 'required|date',
            'dias.*.hora_inicio' => 'nullable|date_format:H:i',
            'dias.*.hora_fin'    => 'nullable|date_format:H:i',
        ], [
            'title.required'  => 'El título es obligatorio.',
            'title.max'       => 'El título no debe exceder 191 caracteres.',
            'evento.required' => 'La descripción del evento es obligatoria.',
            'evento.max'      => 'La descripción no debe exceder 191 caracteres.',
            'color.required'  => 'Debe seleccionar la sección (color) del evento.',
            'dias.required'   => 'Debe registrar al menos un día.',
            'dias.min'        => 'Debe registrar al menos un día.',
            'dias.max'        => 'No se pueden registrar más de ' . self::MAX_DIAS . ' días.',
            'dias.*.fecha.required'          => 'La fecha del día es obligatoria.',
            'dias.*.fecha.date'              => 'La fecha del día no es válida.',
            'dias.*.hora_inicio.date_format' => 'La hora inicial no es válida.',
            'dias.*.hora_fin.date_format'    => 'La hora final no es válida.',
        ]);

        foreach ($data['dias'] as $i => $dia) {
            $inicio = $dia['hora_inicio'] ?? null;
            $fin    = $dia['hora_fin'] ?? null;

            // La hora es "todo o nada": el formulario ya lo valida, pero la API
            // también debe rechazar días a medio configurar (solo inicio o solo
            // fin), que dejarían el día como evento de todo el día.
            if (($inicio && ! $fin) || (! $inicio && $fin)) {
                throw ValidationException::withMessages([
                    "dias.$i.hora_fin" => 'Indique la hora inicial y final, o deje ambas vacías.',
                ]);
            }

            if ($inicio && $fin && $fin <= $inicio) {
                throw ValidationException::withMessages([
                    "dias.$i.hora_fin" => 'La hora final debe ser posterior a la inicial.',
                ]);
            }
        }

        $fechas = array_map(fn ($d) => $d['fecha'], $data['dias']);
        if (count($fechas) !== count(array_unique($fechas))) {
            throw ValidationException::withMessages([
                'dias' => 'No se puede repetir la misma fecha en dos días.',
            ]);
        }

        return $data;
    }

    /**
     * Rango global derivado para el calendario y los filtros por año: del primer
     * día a la hora inicial, hasta el último día a la hora final.
     *
     * @return array{0: string, 1: string} [start, end] en formato datetime-local.
     */
    private function rangoDesdeDias(array $dias): array
    {
        $ordenados = collect($dias)->sortBy('fecha')->values();
        $primero   = $ordenados->first();
        $ultimo    = $ordenados->last();

        $inicio = $primero['fecha'] . 'T' . ($primero['hora_inicio'] ?: '00:00');
        $fin    = $ultimo['fecha'] . 'T' . ($ultimo['hora_fin'] ?: '23:59');

        return [$inicio, $fin];
    }

    /**
     * Sincroniza los días programados de la agenda. Al eliminar un día se
     * eliminan también sus archivos (físico + registro por cascada de la FK).
     */
    private function guardarDias(Agenda $agenda, array $dias): void
    {
        $normalizados = collect($dias)->map(fn ($d) => [
            'fecha'       => $d['fecha'],
            'hora_inicio' => $d['hora_inicio'] ?: null,
            'hora_fin'    => $d['hora_fin'] ?: null,
        ])->sortBy('fecha')->values();

        $fechas = $normalizados->pluck('fecha')->all();

        DB::transaction(function () use ($agenda, $normalizados, $fechas) {
            $idsEliminados = $agenda->dias()->whereNotIn('fecha', $fechas)->pluck('id');

            if ($idsEliminados->isNotEmpty()) {
                AgendaEvidencia::whereIn('agenda_dia_id', $idsEliminados)
                    ->get()
                    ->each(fn ($ev) => Storage::delete('public/' . $ev->enlace));

                $agenda->dias()->whereIn('id', $idsEliminados)->delete();
            }

            foreach ($normalizados as $dia) {
                $agenda->dias()->updateOrCreate(
                    ['fecha' => $dia['fecha']],
                    ['hora_inicio' => $dia['hora_inicio'], 'hora_fin' => $dia['hora_fin']],
                );
            }
        });
    }
}
