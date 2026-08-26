<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Taller;

class TallerController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
        
    }
    public function index()
    {
        $tallers = Taller::where('estado', '1')->orderby('id')->get();
        return view('taller.index')->with('tallers',$tallers);
    }

    public function create()
    {
        return view('taller.create');
    }

    
    public function store(Request $request)
    {
        $tallers = new Taller();
        $tallers->nombreTaller = $request->getClientOriginalName();
        $tallers->plan = $request->file('plan')->store('public/planA'); 
        $tallers->informe = 20;
        $tallers->fotoTaller = 21;
        $tallers->enlaceVideo = 22;
        $tallers->fechaSupervicion = $request->get('fechaSupervicion');
        $tallers->docente = $request->get('docente');
        $tallers->idInstitucion = 1;
        $tallers->idUser = 1;
        $tallers->save();
        
        return redirect('/tallers');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\taller  $taller
     * @return \Illuminate\Http\Response
     */
    public function show(taller $taller)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\taller  $taller
     * @return \Illuminate\Http\Response
     */
    public function edit(taller $taller)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\taller  $taller
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, taller $taller)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\taller  $taller
     * @return \Illuminate\Http\Response
     */
    public function destroy(taller $taller)
    {
        //
    }
}
