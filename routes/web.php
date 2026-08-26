<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EvidenciaController;
use App\Http\Controllers\AgendaViewController;
use App\Http\Controllers\AgendaController;
use App\Http\Controllers\AccionController;
use App\Http\Controllers\DifusionController;
use App\Http\Controllers\InformeController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\ProduccionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\InstitucionController;



#nuevo sector
use App\Http\Controllers\SectorController;



/* Cambios Para internet Institucion */

use App\Http\Controllers\InternetInstitucionesController;
use Illuminate\Support\Facades\DB;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get("/sector", ['App\Http\Controllers\SectorController'::class, "index"]

)->name('sector.index');

Route::get('/', function () {
    return view('auth.login');
     //return view('welcomenp');
});


Route::get('/home', function () {
    return view('auth.login');
     //return view('welcomenp');        
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified'
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});

Route::get('/inicio', function () {
    return view('admin.index');
});

//RUTAS DE INSITUCIONES/INTERNET-----------------------------------------------

Route::get('interInstitucion/{id}/ver_mas', ['App\Http\Controllers\InternetInstitucionesController'::class, 'ver_mas'])->name('internet.ver_mas');

Route::get('interInstitucion/buscar', [App\Http\Controllers\InternetInstitucionesController::class, 'buscar']);

Route::resource('interInstitucion',InternetInstitucionesController::class);

Route::post('/interInstitucion/buscar', function (Illuminate\Http\Request $request) {

    $codModular = $request->input('Codigo_Modular');

    //$revision["rev"] = DB::select("select * from internet_institucionesh where codigoModular = $codModular");
    $data["datos"] = DB::select("select * from institucions where codModular = $codModular");
    /*if (count($revision) == 0) {

        $data["datos"] = DB::select("select * from institucions where codModular = $codModular");

        return view('interInstitucion/create', $data);
    }else {
        return print('Institucion registrada');
    }*/
    return view('interInstitucion/create', $data); //$revision);


})->name('interInstitucion.buscar');

Route::post('/interInstitucion/buscar_index', function (Illuminate\Http\Request $request) {

    $codModular = $request->input('Codigo_Modular');

    $revision["rev"] = DB::select("select * from internet_institucionesh where codigoModular = $codModular");
    $data["datos"] = DB::select("select * from institucions where codModular = $codModular");

    $usuario = Auth::user()->name;
    $ugel = Auth::user()->provincia;

        $dat_user = DB::select("select roles.name as rol
                                from users
                                inner join model_has_roles as m
                                on m.model_id= users.id
                                inner join roles
                                on m.role_id = roles.id
                                where users.name = '$usuario'");

        $authorizedRoles = ['Admin', 'EspecDRE', 'EspecUGEL', 'Director'];
        $user_role = $dat_user[0]->rol; // Obtén el rol del primer registro (siempre debería haber al menos uno)

        if (in_array($user_role, $authorizedRoles)) {

            $datos_gen = [];

            if ($user_role == 'Admin' || $user_role == 'EspecDRE') {
                $datos_gen = DB::table('internet_institucionesh')
                            ->where('codigoModular', $codModular)
                            ->paginate(9);

            } elseif ($user_role == 'EspecUGEL') {
                $datos_gen = DB::table('internet_institucionesh')
                            ->where('provincia', $ugel)
                            ->and('codigoModular', $codModular)
                            ->paginate(9);

            } elseif ($user_role == 'Director') {
                $datos_gen = DB::table('internet_institucionesh')
                            ->where('usuario', $usuario)
                            ->and('codigoModular', $codModular)
                            ->paginate(9);
            }

            return view('interInstitucion.index', [
                'datos_gen' => $datos_gen,
                'dat_user' => $user_role
            ]);

        }



    /*if (count($revision) == 0) {

        $data["datos"] = DB::select("select * from institucions where codModular = $codModular");

        return view('interInstitucion/create', $data);
    }else {
        return print('Institucion registrada');
    }*/
    return view('interInstitucion/index', $data, $revision);


})->name('interInstitucion.buscar_index');

//Route::get('interInstitucion/buscar/{codigoModular}', 'InternetInstitucionesController@buscar1');

//--------------------------------------------------------------------------------

Route::resource('institucions','App\Http\Controllers\InstitucionController');
Route::resource('tallers','App\Http\Controllers\TallerController');
Route::resource('users','App\Http\Controllers\UserController');
Route::resource('plans','App\Http\Controllers\PlanController');
Route::resource('informes','App\Http\Controllers\InformeController');
Route::resource('accions','App\Http\Controllers\AccionController');

Route::resource('evidencias','App\Http\Controllers\EvidenciaController');
Route::resource('sector','App\Http\Controllers\SectorController');


#nuevo sector Route::resource('sectores','App\Http\Controllers\SectorController');

// web.php
Route::resource('sectores', SectorController::class);


Route::resource('produccions','App\Http\Controllers\ProduccionController');
Route::resource('difusions','App\Http\Controllers\DifusionController');
Route::resource('usuarios','App\Http\Controllers\UsuarioController');

Route::resource('dashboard_main','App\Http\Controllers\DashboardController');

Route::get("/dashboard-index", ['App\Http\Controllers\DashboardController'::class, "index"])->middleware('auth')->name('dashboard.index');
Route::get("/dashboard-dre", ['App\Http\Controllers\DashboardController'::class, "dre"])->middleware('auth')->name('dashboard.dre');
Route::get("/dashboard-ugel", ['App\Http\Controllers\DashboardController'::class, "ugel"])->middleware('auth')->name('dashboard.ugel');
Route::get("/dashboard-pc", ['App\Http\Controllers\DashboardController'::class, "pc"])->middleware('auth')->name('dashboard.pc');

Route::resource('agendas','App\Http\Controllers\AgendaController');
Route::get('agenda/view', [App\Http\Controllers\AgendaViewController::class, 'index']);
Route::post('agendas/update', [App\Http\Controllers\AgendaController::class, 'update']);

Route::get("/buscar-agenda-general",['App\Http\Controllers\AgendaViewController'::class, "buscarGeneral"])->name('buscarAgendaGeneral')->middleware('auth');
Route::get("/buscar-agenda-ugel",['App\Http\Controllers\AgendaViewController'::class, "buscarUgel"])->name('buscarAgendaUgel')->middleware('auth');

Route::get("/agenda-ugel", ['App\Http\Controllers\AgendaViewController'::class, "ugel"])->middleware('auth');
Route::get("/agenda-general", ['App\Http\Controllers\AgendaViewController'::class, "general"])->middleware('auth');

Route::get('produccions/{id}/download', ['App\Http\Controllers\ProduccionController'::class, 'download'])->name('produccions.download');
Route::get('accions/{id}/download', ['App\Http\Controllers\AccionController'::class, 'download'])->name('accions.download');
Route::get('evidencias/{id}/download', ['App\Http\Controllers\EvidenciaController'::class, 'download'])->name('evidencias.download');

# nuevo sector
Route::get('sectores/{id}/download', ['App\Http\Controllers\SectorController'::class, 'download'])->name('sectores.download');


Route::get('informes/{id}/download', ['App\Http\Controllers\InformeController'::class, 'download'])->name('informes.download');
Route::get('plans/{id}/download', ['App\Http\Controllers\PlanController'::class, 'download'])->name('plans.download');

Route::get("/buscar-accion",['App\Http\Controllers\AccionController'::class, "buscar"])->name('buscarAccion')->middleware('auth');
Route::get("/buscar-accion-general",['App\Http\Controllers\AccionController'::class, "buscarGeneral"])->name('buscarAccionGeneral')->middleware('auth');

Route::get("/buscar-difusion",['App\Http\Controllers\DifusionController'::class, "buscar"])->name('buscarDifusion')->middleware('auth');
Route::get("/buscar-difusion-general",['App\Http\Controllers\DifusionController'::class, "buscarGeneral"])->name('buscarDifusionGeneral')->middleware('auth');

Route::get("/buscar-informe",['App\Http\Controllers\InformeController'::class, "buscar"])->name('buscarInforme')->middleware('auth');
Route::get("/buscar-informe-general",['App\Http\Controllers\InformeController'::class, "buscarGeneral"])->name('buscarInformeGeneral')->middleware('auth');
Route::get("/buscar-informe-ugel",['App\Http\Controllers\InformeController'::class, "buscarUgel"])->name('buscarInformeUgel')->middleware('auth');
Route::get("/buscar-informe-director",['App\Http\Controllers\InformeController'::class, "buscarDirector"])->name('buscarInformeDirector')->middleware('auth');

Route::get("/buscar-plan",['App\Http\Controllers\PlanController'::class, "buscar"])->name('buscarPlan')->middleware('auth');
Route::get("/buscar-plan-general",['App\Http\Controllers\PlanController'::class, "buscarGeneral"])->name('buscarPlanGeneral')->middleware('auth');
Route::get("/buscar-plan-ugel",['App\Http\Controllers\PlanController'::class, "buscarUgel"])->name('buscarPlanUgel')->middleware('auth');
Route::get("/buscar-plan-director",['App\Http\Controllers\PlanController'::class, "buscarDirector"])->name('buscarPlanDirector')->middleware('auth');

Route::get("/buscar-evidencia",['App\Http\Controllers\EvidenciaController'::class, "buscar"])->name('buscarEvidencia')->middleware('auth');
Route::get("/buscar-evidencia-general",['App\Http\Controllers\EvidenciaController'::class, "buscarGeneral"])->name('buscarEvidenciaGeneral')->middleware('auth');
Route::get("/buscar-evidencia-ugel",['App\Http\Controllers\EvidenciaController'::class, "buscarUgel"])->name('buscarEvidenciaUgel')->middleware('auth');
Route::get("/buscar-evidencia-director",['App\Http\Controllers\EvidenciaController'::class, "buscarDirector"])->name('buscarEvidenciaDirector')->middleware('auth');

# nuevo sector
Route::get("/buscar-sector",['App\Http\Controllers\SectorController'::class, "buscar"])->name('buscarSector')->middleware('auth');
Route::get("/buscar-sector-general",['App\Http\Controllers\SectorController'::class, "buscarGeneral"])->name('buscarSectorGeneral')->middleware('auth');
Route::get("/buscar-sector-ugel",['App\Http\Controllers\SectorController'::class, "buscarUgel"])->name('buscarSectorUgel')->middleware('auth');
Route::get("/buscar-sector-director",['App\Http\Controllers\SectorController'::class, "buscarDirector"])->name('buscarSectorDirector')->middleware('auth');




Route::get("/buscar-produccion",['App\Http\Controllers\ProduccionController'::class, "buscar"])->name('buscarProduccion')->middleware('auth');
Route::get("/buscar-produccion-general",['App\Http\Controllers\ProduccionController'::class, "buscarGeneral"])->name('buscarProduccionGeneral')->middleware('auth');

Route::get("/buscar-usuario",['App\Http\Controllers\UserController'::class, "buscar"])->name('buscarUser')->middleware('auth');


Route::get("/produccion-general", ['App\Http\Controllers\ProduccionController'::class, "general"])->middleware('auth')->name('produccions.view');
Route::get("/accion-general", ['App\Http\Controllers\AccionController'::class, "general"])->middleware('auth')->name('accions.view');
Route::get("/accion-dre", ['App\Http\Controllers\AccionController'::class, "dre"])->middleware('auth')->name('accions.dre');
Route::get("/plan-general", ['App\Http\Controllers\PlanController'::class, "general"])->middleware('auth')->name('plans.view');
Route::get("/informe-general", ['App\Http\Controllers\InformeController'::class, "general"])->middleware('auth')->name('informes.view');
Route::get("/evidencia-general", ['App\Http\Controllers\EvidenciaController'::class, "general"])->middleware('auth')->name('evidencias.view');
# nuevo sector
Route::get("/sector-general", ['App\Http\Controllers\SectorController'::class, "general"])->middleware('auth')->name('sectores.view');


Route::get("/difusion-general", ['App\Http\Controllers\DifusionController'::class, "general"])->middleware('auth')->name('accions.view');




Route::get("/informe-ugel", ['App\Http\Controllers\InformeController'::class, "ugel"])->middleware('auth')->name('informes.ugel');
Route::get("/informe-director", ['App\Http\Controllers\InformeController'::class, "director"])->middleware('auth')->name('informes.director');

Route::get("/plan-ugel", ['App\Http\Controllers\PlanController'::class, "ugel"])->middleware('auth')->name('plans.ugel');
Route::get("/plan-director", ['App\Http\Controllers\PlanController'::class, "director"])->middleware('auth')->name('plans.director'); 

Route::get("/evidencia-ugel", ['App\Http\Controllers\EvidenciaController'::class, "ugel"])->middleware('auth')->name('evidencias.ugel');
Route::get("/evidencia-director", ['App\Http\Controllers\EvidenciaController'::class, "director"])->middleware('auth')->name('evidencias.director');

#nuevo sector
Route::get("/sector-ugel", ['App\Http\Controllers\SectorController'::class, "ugel"])->middleware('auth')->name('sectores.ugel');
Route::get("/sector-director", ['App\Http\Controllers\SectorController'::class, "director"])->middleware('auth')->name('sectores.director');



# nuevo sector
Route::get('/buscar-instituciones-sector', [SectorController::class, 'buscador'])->name('buscarInstitucionesSector');

/* BUSQUEDA DE AGENDA */
Route::get('/agenda-general', [AgendaViewController::class, 'general'])->name('agenda.general');
Route::get('/buscar-agenda-general', [AgendaViewController::class, 'buscarGeneral'])->name('buscarAgendaGeneral');
Route::get('/agenda-ugel', [AgendaViewController::class, 'ugel'])->name('agenda.ugel');
Route::get('/buscar-agenda-ugel', [AgendaViewController::class, 'buscarUgel'])->name('buscarAgendaUgel');

Route::get('/get-ugels-ag', [AgendaViewController::class, 'obtenerUgels'])->name('get-ugels-ag');
Route::get('/get-institucions', [AgendaViewController::class, 'obtenerInstitucions'])->name('get-institucions');
Route::get('/buscar-instituciones-agenda', [AgendaViewController::class, 'buscadorinstitucion'])->name('buscarInstitucionesAgenda');
Route::get('/buscar-docentes-agenda', [AgendaViewController::class, 'buscadordocente'])->name('buscarDocentesAgenda');
Route::get('/buscar-institucion-por-ugel-ag', [AgendaViewController::class, 'buscarInstitucionporUgel'])->name('buscarInstitucionporUgel-ag');
Route::get('/buscar-docente-por-institucion-ag', [AgendaViewController::class, 'buscarDocenteporInstitucion'])->name('buscarDocenteporInstitucion-ag');
Route::get('/exportar-agendas', [AgendaViewController::class, 'exportarTodos'])->name('exportar.agendas');
/* BUSQUEDA DE AGENDA */

/* BUSQUEDA DE ACCION */
Route::get('/get-ugels-acc', [AccionController::class, 'obtenerUgels'])->name('get-ugels-acc');
Route::get('/buscar-instituciones-accion', [AccionController::class, 'buscadorinstitucion'])->name('buscarInstitucionesAccion');
Route::get('/buscar-docentes-accion', [AccionController::class, 'buscadordocente'])->name('buscarDocentesAccion');

Route::get('/buscar-instituciones-por-ugel-acc', [AccionController::class, 'buscarInstitucionporUgel'])->name('buscarInstitucionporUgel-acc');
Route::get('/buscar-docentes-por-institucion-acc', [AccionController::class, 'buscarDocenteporInstitucion'])->name('buscarDocenteporInstitucion-acc');

Route::get('/exportar-filtrado-total', [AccionController::class, 'exportarFiltradoTotal'])->name('exportar.filtrado.total');


/* BUSQUEDA DE ACCION */

/* BUSQUEDA DE DIFUSION */
Route::get('/get-ugels-dif', [DifusionController::class, 'obtenerUgels'])->name('get-ugels-dif');
Route::get('/buscar-instituciones-difusion', [DifusionController::class, 'buscadorinstitucion'])->name('buscarInstitucionesDifusion');
Route::get('/buscar-docentes-difusion', [DifusionController::class, 'buscadordocente'])->name('buscarDocentesDifusion');

Route::get('/buscar-instituciones-por-ugel-dif', [DifusionController::class, 'buscarInstitucionporUgel'])->name('buscarInstitucionporUgel-dif');
Route::get('/buscar-docentes-por-institucion-dif', [DifusionController::class, 'buscarDocenteporInstitucion'])->name('buscarDocenteporInstitucion-dif');
Route::get('/exportar-evidencias', [EvidenciaController::class, 'exportarTodos'])->name('exportar.evidencias');

// Rutas para difusión
Route::get('/difusion-general', [DifusionController::class, 'general'])->name('difusion-general');
Route::get('/buscar-difusion-general', [DifusionController::class, 'buscarGeneral'])->name('buscarDifusionGeneral');



/* BUSQUEDA DE DIFUSION */

/* BUSQUEDA DE EVIDENCIA */
Route::get('/get-ugels-evi', [EvidenciaController::class, 'obtenerUgels'])->name('get-ugels-evi');
Route::get('/buscar-instituciones-evidencia', [EvidenciaController::class, 'buscadorinstitucion'])->name('buscarInstitucionesEvidencia');
Route::get('/buscar-docentes-evidencia', [EvidenciaController::class, 'buscadordocente'])->name('buscarDocentesEvidencia');

Route::get('/buscar-instituciones-por-ugel-evi', [EvidenciaController::class, 'buscarInstitucionporUgel'])->name('buscarInstitucionporUgel-evi');
Route::get('/buscar-docentes-por-institucion-evi', [EvidenciaController::class, 'buscarDocenteporInstitucion'])->name('buscarDocenteporInstitucion-evi');
Route::get('/exportar-evidencias', [EvidenciaController::class, 'exportarTodos'])->name('exportar.evidencias');
/* BUSQUEDA DE EVIDENCIA */

/* BUSQUEDA DE SECTOR  evi = sec */
Route::get('/get-ugels-sec', [SectorController::class, 'obtenerUgels'])->name('get-ugels-sec');
Route::get('/buscar-instituciones-sector', [SectorController::class, 'buscadorinstitucion'])->name('buscarInstitucionesSector');
Route::get('/buscar-docentes-sector', [SectorController::class, 'buscadordocente'])->name('buscarDocentesSector');

Route::get('/buscar-instituciones-por-ugel-sec', [SectorController::class, 'buscarInstitucionporUgel'])->name('buscarInstitucionporUgel-sec');
Route::get('/buscar-docentes-por-institucion-sec', [SectorController::class, 'buscarDocenteporInstitucion'])->name('buscarDocenteporInstitucion-sec');
Route::get('/buscar-docentes-sector', [SectorController::class, 'buscadordocente'])->name('buscarDocentesSector');
Route::get('/exportar-sectores', [SectorController::class, 'exportarTodos'])->name('exportar.sectores');
/* BUSQUEDA DE SECTOR */





/* BUSQUEDA DE INFORME */
Route::get('/get-ugels-inf', [InformeController::class, 'obtenerUgels'])->name('get-ugels-inf');
Route::get('/buscar-instituciones-informe', [InformeController::class, 'buscadorinstitucion'])->name('buscarInstitucionesInforme');
Route::get('/buscar-docentes-informe', [InformeController::class, 'buscadordocente'])->name('buscarDocentesInforme');

Route::get('/buscar-instituciones-por-ugel-inf', [InformeController::class, 'buscarInstitucionporUgel'])->name('buscarInstitucionporUgel-inf');
Route::get('/buscar-docentes-por-institucion-inf', [InformeController::class, 'buscarDocenteporInstitucion'])->name('buscarDocenteporInstitucion-inf');
Route::get('/exportar-biblioteca', [InformeController::class, 'exportarTodos'])->name('exportar.biblioteca');

/* BUSQUEDA DE INFORME */

/* BUSQUEDA DE PLAN */
Route::get('/get-ugels-plan', [PlanController::class, 'obtenerUgels'])->name('get-ugels-plan');
Route::get('/buscar-instituciones-plan', [PlanController::class, 'buscadorinstitucion'])->name('buscarInstitucionesPlan');
Route::get('/buscar-docentes-plan', [PlanController::class, 'buscadordocente'])->name('buscarDocentesPlan');

Route::get('/buscar-instituciones-por-ugel-plan', [PlanController::class, 'buscarInstitucionporUgel'])->name('buscarInstitucionporUgel-plan');
Route::get('/buscar-docentes-por-institucion-pla', [PlanController::class, 'buscarDocenteporInstitucion'])->name('buscarDocenteporInstitucion-pla');
Route::get('/exportar-planes', [PlanController::class, 'exportarTodos'])->name('exportar.planes');

/* BUSQUEDA DE PLAN */

/* BUSQUEDA DE PRODUCCION */
Route::get('/get-ugels-pro', [ProduccionController::class, 'obtenerUgels'])->name('get-ugels-pro');
Route::get('/buscar-instituciones-produccion', [ProduccionController::class, 'buscadorinstitucion'])->name('buscarInstitucionesProduccion');
Route::get('/buscar-docentes-produccion', [ProduccionController::class, 'buscadordocente'])->name('buscarDocentesProduccion');
Route::get('/produccion-general', [ProduccionController::class, 'general'])->name('produccion.general');
Route::get('/buscar-produccion-general', [ProduccionController::class, 'buscarGeneral'])->name('buscarProduccionGeneral');
Route::get('/buscadordocente', [ProduccionController::class, 'buscadordocente'])->name('buscadordocente');

Route::get('/buscar-instituciones-por-ugel-pro', [ProduccionController::class, 'buscarInstitucionporUgel'])->name('buscarInstitucionporUgel-pro');
Route::get('/buscar-docentes-por-institucion-pro', [ProduccionController::class, 'buscarDocenteporInstitucion'])->name('buscarDocenteporInstitucion-pro');
Route::get('/buscarDocenteporInstitucion', [ProduccionController::class, 'buscarDocenteporInstitucion'])->name('buscarDocenteporInstitucion');

Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
Route::get('/usuarios/{id}/estado', 'App\Http\Controllers\UserController@cambiarEstado')->name('cambiarEstado');

Route::get("/sector-general", [App\Http\Controllers\SectorController::class, "general"])->middleware('auth')->name('sectores.view');
Route::get("/sector-dre", [App\Http\Controllers\SectorController::class, "dre"])->middleware('auth')->name('sectores.dre');
Route::get("/sector-ugel", [App\Http\Controllers\SectorController::class, "ugel"])->middleware('auth')->name('sectores.ugel');
Route::get("/sector-director", [App\Http\Controllers\SectorController::class, "director"])->middleware('auth')->name('sectores.director');
Route::resource('sectores', App\Http\Controllers\SectorController::class);
Route::get('/exportar-producciones', [ProduccionController::class, 'exportarTodos'])->name('exportar.producciones');

/*BUSQUEDA DE INSTITUCION POR UGEL*/

Route::get('/instituciones', [InstitucionController::class, 'index'])->name('institucion.index');
Route::get('/buscar-instituciones-por-ugel-ins', [InstitucionController::class, 'buscarInstitucionporUgel'])->name('buscarInstitucionporUgel-ins');





/* BUSQUEDA DE PRODUCCION */

Route::post("/export/{tipo}", ['App\Http\Controllers\DashboardController'::class, "exportData"])->name('export');


Route::get('/get-ugels-users', [UserController::class, 'obtenerUgels'])->name('get-ugels-users');
Route::get('/export-users', [App\Http\Controllers\UserController::class, 'exportUsers'])->name('exportUsers');

Route::get('/api/instituciones/{codModular}', function ($codModular) {
    $codModular = trim($codModular);
    $results = \App\Models\Institucion::where('codModular', $codModular)
        ->orWhere('codModular', str_pad($codModular, 7, '0', STR_PAD_LEFT))
        ->orWhere('codModular', ltrim($codModular, '0'))
        ->get();
    return response()->json($results);
})->name('api.instituciones.get');