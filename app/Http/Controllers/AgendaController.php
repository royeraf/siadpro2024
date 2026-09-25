<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HasScopeTabs;
use App\Models\Agenda;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class AgendaController extends Controller
{
    use HasScopeTabs;

    public function __construct(){
        $this->middleware('auth');
        $this->middleware('can:agendas.index')->only('index');
        $this->middleware('can:agendas.create')->only('create', 'store');
        $this->middleware('can:agendas.edit')->only('update');
        // landing() se queda sin permiso propio: valida por sí misma con
        // abort_if(empty($tabs)) contra las pestañas a las que el usuario
        // realmente tenga acceso (ver landing() más abajo).
    }
    public function index()
    {
        $usuario = Auth::user()->id;

        // Antes era Agenda::all() (30k+ filas) filtrado en PHP sobre la
        // colección; el filtro se hace ahora en SQL.
        $events = Agenda::where('idUser', $usuario)
            ->where('estado', '1')
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
            ]);

        $tabs = $this->tabsAgenda('index');

        return Inertia::render('Agenda/Index', [
            'events'   => $events,
            'tabs'     => $tabs,
            'secciones' => Agenda::SECCIONES,
            'can'      => [
                'create'  => Auth::user()->can('agendas.create'),
                'edit'    => Auth::user()->can('agendas.edit'),
                'destroy' => Auth::user()->can('agendas.destroy'),
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

    public function create()
    {
        return view('agenda.create');
    }

    public function store(Request $request)
    {
        $agenda = new Agenda();
        $agenda->title = $request->title;
        $agenda->evento = $request->evento;
        $agenda->color = $request->input('color', $request->input('seccion'));
        $agenda->start = $request->start;
        $agenda->end = $request->end;
        $agenda->idUser = Auth::user()->id;
        $agenda->estado = 1;
        $agenda->save();
        return redirect('/agendas')->with('success', '¡Evento registrado con éxito!');
    }
    public function update(Request $request, agenda $agenda)
    {
        $agenda = Agenda::findOrFail($request->id);
        // agendas.edit (exigido por el constructor) no distingue de quién es el
        // registro: sin esto, cualquier usuario con permiso de escritura podía
        // modificar o borrar la agenda de cualquier otro, con solo conocer el id.
        abort_unless((int) $agenda->idUser === Auth::id(), 403);

            $mensaje = '¡Evento actualizado con éxito!';

            if ($request->get('delete') == 'on') {
                abort_unless(Auth::user()->can('agendas.destroy'), 403);
                $agenda->estado = '0';
                $agenda->idUser = Auth::user()->id;
                $agenda->save();
                $mensaje = '¡Evento eliminado con éxito!';
            }
            else{
                $agenda->title = $request->get('title');
                $agenda->evento = $request->get('evento');
                $agenda->color = $request->input('color', $request->input('seccion'));
                $agenda->start = $request->get('start');
                $agenda->end = $request->get('end');
                $agenda->idUser = Auth::user()->id;
                $agenda->save();
            }
            return redirect('/agendas')->with('success', $mensaje);
    }
}

