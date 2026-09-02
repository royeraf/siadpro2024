<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HasScopeTabs;
use App\Models\Agenda;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class AgendaController extends Controller
{
    use HasScopeTabs;

    public function __construct(){
        $this->middleware('auth');
        //$this->middleware('can:agendas.index')->only('index');
    }
    public function index()
    {
        $usuario = Auth::user()->id;
        $events = Agenda::all()->where('idUser',$usuario)->where('estado', '1');
        $tabs = $this->tabsAgenda('index');
        return view('agenda.index')->with('events',$events)->with('tabs', $tabs);
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
        $agenda->nomDocente = Auth::user()->name;
        $agenda->evento = $request->evento;
        $agenda->color = $request->color;
        $agenda->institucion = Auth::user()->institucion;
        $agenda->start = $request->start;
        $agenda->end = $request->end;
        $agenda->idUser = Auth::user()->id;
        $agenda->estado = 1;
        $agenda->save();
        return redirect('/agendas');
    }
    public function update(Request $request, agenda $agenda)
    {
        $agenda = Agenda::findOrFail($request->id);
            if ($request->get('delete') == 'on') {
                $agenda->estado = '0';
                $agenda->idUser = Auth::user()->id;
                $agenda->save();    
            }
            else{
                $agenda->title = $request->get('title');
                $agenda->nomDocente = Auth::user()->name;
                $agenda->evento = $request->get('evento');
                $agenda->color = $request->get('color');
                $agenda->start = $request->get('start');
                $agenda->end = $request->get('end');
                $agenda->institucion = Auth::user()->institucion;
                $agenda->idUser = Auth::user()->id;
                $agenda->save();
            }
            return redirect('/agendas');
    }
}

