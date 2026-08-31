<?php

namespace App\Http\Controllers;

use App\Models\Agenda;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class AgendaController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
        //$this->middleware('can:agendas.index')->only('index');
    }
    public function index()
    {
        $usuario = Auth::user()->id;
        $events = Agenda::all()->where('idUser',$usuario)->where('estado', '1');
        return view('agenda.index')->with('events',$events);
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

