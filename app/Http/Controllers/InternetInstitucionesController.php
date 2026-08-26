<?php

namespace App\Http\Controllers;

use App\Models\internetInstituciones;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Termwind\Components\Dd;

class InternetInstitucionesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    /*public function __construct(){

        $this->middleware('auth');
        $this->middleware('can:interInstituciones.index')->only('index');
        $this->middleware('can:interInstituciones.create')->only('create', 'store');
        $this->middleware('can:interInstituciones.edit')->only('edit', 'update');
        $this->middleware('can:interInstituciones.destroy')->only('destroy');
        $this->middleware('can:interInstituciones.view')->only('general');

    }*/

    public function ver_mas($id){

        $id;

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

            if ($user_role == 'Admin') {
                $tipo_rol['tipo_rol'] = 'Admin';
                $dat_inst['dat_inst'] = DB::select("select * from internet_institucionesh where id = $id");

            } elseif ($user_role == 'EspecDRE') {
                $tipo_rol['tipo_rol'] = 'EspecDRE';
                $dat_inst['dat_inst'] = DB::select("select * from internet_institucionesh where id = $id");

            } elseif ($user_role == 'EspecUGEL') {
                $tipo_rol['tipo_rol'] = 'EspecUGEL';
                $dat_inst['dat_inst'] = DB::select("select * from internet_institucionesh where id = $id and provincia = '$ugel'");

            } elseif ($user_role == 'Director') {
                $tipo_rol['tipo_rol'] = 'Director';
                $dat_inst['dat_inst'] = DB::select("select * from internet_institucionesh where id = $id and usuario = '$usuario'");

            }

        }

        return view('interInstitucion.index_ver_mas',$dat_inst, $tipo_rol);

    }


    public function index()
    {

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
                $datos_gen = DB::table('internet_institucionesh')->paginate(9);

            } elseif ($user_role == 'EspecUGEL') {
                $datos_gen = DB::table('internet_institucionesh')
                            ->where('provincia', $ugel)
                            ->paginate(9);

            } elseif ($user_role == 'Director') {
                $datos_gen = DB::table('internet_institucionesh')
                            ->where('usuario', $usuario)
                            ->paginate(9);
            }

            return view('interInstitucion.index', [
                'datos_gen' => $datos_gen,
                'dat_user' => $user_role
            ]);

        } else {
            session()->flash('mensajeinternet', 'Acceso no autorizado a esta seccion');

            return back();
        }

    }

    public function buscar(){

        return view('interInstitucion.buscar');
    }



    /*public function buscar1($codigoModular)
    {
        // Realizar la búsqueda en la base de datos
        $institucion = DB::table('internet_institucionesh')
            ->where('codigoModular', $codigoModular)
            ->first();

        return response()->json(['institucion' => $institucion]);
    }*/
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {


        return view('interInstitucion/create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
{
    //dd($request->all());
    $data = $request->all();

    $usuario = Auth::user()->name;

    $codigoModular = $data['Codigo_Modular_Data'];
    $nombreInstitucion = $data['NombreInstitucion'];
    $nivelModalidad = $data['Nivel_Modalidad'];
    $departamento = $data['departamento'];
    $provincia = $data['provincia'];
    $distrito = $data['distrito'];

    $centroPoblado = $data['centroPoblado'];
    $ugel = $data['ugel'];



    $proveedorServicio = $data['Proveedor'];
    $tipoLinea = $data['tipo_linea'];

    if ($tipoLinea == 'Otros') {

        $otros = $data['otros'];

    }else{
        $otros = $data['tipo_linea'];
    };

    $megasContratadas = $data['Megas_Contratadas'];
    $costoMensual = $data['costoMensual'];
    $costoAnual = $costoMensual*12;
    $coordenadaX = $data['latitude'];
    $coordenadaY = $data['longitude'];
    $fechaInstalacion = $data['fechaInstalacion'];
    $inicioContrato = $data['inicioContrato'];
    $finalContrato = $data['finalContrato'];

    $tipoDocumento = $data['tipoDocumento'];
    $nmrNombreResolucion = $data['nmrNombreResolucion'];
    $descripcionResolucion = $data['descripcion'];

    // Manejar el archivo PDF
    if ($request->hasFile('archivoDocumento') && $request->file('archivoDocumento')->isValid()) {
        $archivoPDF = $request->file('archivoDocumento');

        // Verificar si el archivo es un PDF por su extensión
        $extension = $archivoPDF->getClientOriginalExtension();

        if ($extension === 'pdf') {
            // El archivo es un PDF, puedes continuar con el proceso de almacenamiento
            $nombreOriginal = $archivoPDF->getClientOriginalName();

            // Genera un nombre único basado en la fecha y hora actual
            $nombreUnico = date('YmdHis') . '_' . $nombreOriginal;

            // Limpia el nombre del archivo para eliminar caracteres especiales y espacios en blanco
            $nombreLimpio = preg_replace('/[^A-Za-z0-9_\-\.]/', '', $nombreUnico);

            // Reemplaza espacios en blanco con guiones bajos
            $nombreArchivo = str_replace(' ', '_', $nombreLimpio);

            $archivoPDF->move(public_path('archivos_pdf'), $nombreArchivo);
            $archivoDocumento = $nombreArchivo;
        } else {
            // El archivo no es un PDF, muestra un mensaje de error o realiza alguna acción apropiada.
            // Por ejemplo:
            return redirect()->back()->with('error', 'El archivo debe ser un PDF.');
        }
    } else {
        $archivoDocumento = null; // No se ha subido un archivo
    }



    DB::table('internet_institucionesh')->insert([

        'usuario' => $usuario,

        'codigoModular' => $codigoModular,
        'nombreInstitucion' => $nombreInstitucion,
        'nivelModalidad' => $nivelModalidad,
        'departamento' => $departamento,

        'provincia' => $provincia,
        'distrito' => $distrito,
        'centroPoblado' => $centroPoblado,
        'ugel' => $ugel,

        'proveedorServicio' => $proveedorServicio,
        'megasContratadas' => $megasContratadas,
        'costoMensual' => $costoMensual,
        'costoAnual' => $costoAnual,
        'tipoLinea' => $otros,
        'coordenadaX' => $coordenadaX,
        'coordenadaY' => $coordenadaY,
        'fechaInstalacion' => $fechaInstalacion,
        'inicioContrato' => $inicioContrato,
        'finalContrato' => $finalContrato,

        'tipoDocumento' => $tipoDocumento,
        'nmrNombreResolucion' => $nmrNombreResolucion,
        'descripcionResolucion' => $descripcionResolucion,
        'archivoPDF' => $archivoDocumento,
    ]);

    return view('interInstitucion.buscar');
}

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\internetInstituciones  $internetInstituciones
     * @return \Illuminate\Http\Response
     */
    public function show(internetInstituciones $internetInstituciones)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\internetInstituciones  $internetInstituciones
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $informacion["info"] = DB::select("select * from internet_institucionesh where id = $id");

        return view('interInstitucion.edit',$informacion);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\internetInstituciones  $internetInstituciones
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
{
    // Obtener los datos actualizados del formulario
    $data = $request->all();

    // Manejar el archivo PDF

    // Validar la solicitud y obtener los datos del formulario

    $archivoAnterior = DB::table('internet_institucionesh')->where('id', $id)->value('archivoPDF');

    if ($request->hasFile('archivoDocumento') && $request->file('archivoDocumento')->isValid()) {
        // Un nuevo archivo PDF se está cargando
        // Eliminar el archivo PDF anterior si existe
        if ($archivoAnterior) {
            $rutaArchivoAnterior = public_path('archivos_pdf') . '/' . $archivoAnterior;
            if (file_exists($rutaArchivoAnterior)) {
                unlink($rutaArchivoAnterior);
            }
        }

        // Procesar el nuevo archivo PDF
        $archivoPDF = $request->file('archivoDocumento');
        $nombreOriginal = $archivoPDF->getClientOriginalName();

        // Genera un nombre único basado en la fecha y hora actual
        $nombreUnico = date('YmdHis') . '_' . $nombreOriginal;

        // Limpia el nombre del archivo para eliminar caracteres especiales y espacios en blanco
        $nombreLimpio = preg_replace('/[^A-Za-z0-9_\-\.]/', '', $nombreUnico);

        // Reemplaza espacios en blanco con guiones bajos
        $nombreArchivo = str_replace(' ', '_', $nombreLimpio);

        $archivoPDF->move(public_path('archivos_pdf'), $nombreArchivo);
        $archivoDocumento = $nombreArchivo;
    } else {
        // No se ha subido un archivo nuevo, conserva el nombre del archivo anterior
        $archivoDocumento = $archivoAnterior;
    }



    // Actualizar los campos en la base de datos
    DB::table('internet_institucionesh')
        ->where('id', $id)
        ->update([
            'codigoModular' => $data['Codigo_Modular_Data'],
            'nombreInstitucion' => $data['NombreInstitucion'],
            'nivelModalidad' => $data['Nivel_Modalidad'],
            'departamento' => $data['departamento'],
            'provincia' => $data['provincia'],
            'distrito' => $data['distrito'],
            'centroPoblado' => $data['centroPoblado'],
            'ugel' => $data['ugel'],
            'proveedorServicio' => $data['Proveedor'],
            'tipoLinea' => $data['tipo_linea'] == 'Otros' ? $data['otros'] : $data['tipo_linea'],
            'megasContratadas' => $data['Megas_Contratadas'],
            'costoMensual' => $data['costoMensual'],
            'costoAnual' => $data['costoMensual'] * 12,
            'tipoDocumento' => $data['tipoDocumento'],
            'fechaInstalacion' => $data['fechaInstalacion'],
            'inicioContrato' => $data['inicioContrato'],
            'finalContrato' => $data['finalContrato'],
            'nmrNombreResolucion' => $data['nmrNombreResolucion'],
            'descripcionResolucion' => $data['descripcion'],
            'archivoPDF' => $archivoDocumento,
        ]);

        // Agregar mensaje de sesión flash
    Session::flash('success_message', 'La información ha sido actualizada correctamente.');


        $informacion["info"] = DB::select("select * from internet_institucionesh where id = $id");


    // Redirigir a la vista de búsqueda después de actualizar
    return view('interInstitucion/edit', $informacion);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\internetInstituciones  $internetInstituciones
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // Obtener la información del archivo desde la base de datos
        $archivoDocumento = DB::table('internet_institucionesh')->where('id', $id)->value('archivoPDF');

        if ($archivoDocumento) {
            // Eliminar el archivo físico de la carpeta
            $rutaArchivo = public_path('archivos_pdf') . '/' . $archivoDocumento;

            if (file_exists($rutaArchivo)) {
                unlink($rutaArchivo);
            }
        }

        // Eliminar la entrada de la base de datos
        DB::table('internet_institucionesh')->where('id', $id)->delete();

        return redirect('/interInstitucion');
    }

}
