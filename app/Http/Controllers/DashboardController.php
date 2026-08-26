<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Institucion;
use App\Models\Accion;
use App\Models\Evidencia;
use App\Models\Informe;
use App\Models\Plan;
use App\Models\Produccion;
use App\Models\Agenda;

use Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;



class DashboardController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
        $this->middleware('can:dashboard.index')->only('dre');
        $this->middleware('can:dashboard.ugel')->only('ugel');
        $this->middleware('can:dashboard.director')->only('director');
        $this->middleware('can:dashboard.pc')->only('pc');
    }
    public function index()
    {
        $data = DB::select('CALL prdwebregistros()');/* 187 Ambo */
    // Esta consulta es para el total de docentes, directores y pofesor coordinador de cada ugel
        $totaluserByUgel = User::select(
            DB::raw('COUNT(DISTINCT users.id) as totaldocentes'),
            DB::raw('CASE 
                WHEN users.ugel = "Ugel Ambo" THEN "Ugel Ambo"
                WHEN users.ugel = "Ugel Huánuco" THEN "Ugel Huánuco"
                WHEN users.ugel = "Ugel Dos de Mayo" THEN "Ugel Dos de Mayo"
                WHEN users.ugel = "Ugel Huamalies" THEN "Ugel Huamalies"
                WHEN users.ugel = "Ugel Leoncio prado" THEN "Ugel Leoncio prado"
                WHEN users.ugel = "Ugel Pachitea" THEN "Ugel Pachitea"
                WHEN users.ugel = "Ugel Puerto Inca" THEN "Ugel Puerto Inca"
                WHEN users.ugel = "Ugel Yarowilca" THEN "Ugel Yarowilca"
                WHEN users.ugel = "Ugel Marañon" THEN "Ugel Marañon"
                WHEN users.ugel = "Ugel Lauricocha" THEN "Ugel Lauricocha"
                WHEN users.ugel = "Ugel Huacaybamba" THEN "Ugel Huacaybamba"
                ELSE "Otra UGEL"
            END as ugel')
        )
        ->where('users.estado', '1')
        ->where(function ($query) {
            $query->where('users.cargo', 'Director')
                ->orWhere('users.cargo', 'Docente')
                ->orWhere('users.cargo', 'Profesor Coordinador');
        })
        ->groupBy('ugel')
        ->get();
        // Luego, itera sobre los resultados para obtener los valores deseados
        $totalsU = [];
        foreach ($totaluserByUgel as $total) {
            $totalsU[$total->ugel] = $total->totaldocentes;
        }
    // Esta consulta es para el total de docentes, directores y pofesor coordinador de cada ugel
        
    // Esta consulta es para el total de director de cada ugel
        $totaldirByUgel = User::select(
            DB::raw('COUNT(DISTINCT users.id) as totaldocentes'),
            DB::raw('CASE 
                WHEN users.ugel = "Ugel Ambo" THEN "Ugel Ambo"
                WHEN users.ugel = "Ugel Huánuco" THEN "Ugel Huánuco"
                WHEN users.ugel = "Ugel Dos de Mayo" THEN "Ugel Dos de Mayo"
                WHEN users.ugel = "Ugel Huamalies" THEN "Ugel Huamalies"
                WHEN users.ugel = "Ugel Leoncio prado" THEN "Ugel Leoncio prado"
                WHEN users.ugel = "Ugel Pachitea" THEN "Ugel Pachitea"
                WHEN users.ugel = "Ugel Puerto Inca" THEN "Ugel Puerto Inca"
                WHEN users.ugel = "Ugel Yarowilca" THEN "Ugel Yarowilca"
                WHEN users.ugel = "Ugel Marañon" THEN "Ugel Marañon"
                WHEN users.ugel = "Ugel Lauricocha" THEN "Ugel Lauricocha"
                WHEN users.ugel = "Ugel Huacaybamba" THEN "Ugel Huacaybamba"
                ELSE "Otra UGEL"
            END as ugel')
        )
        ->where('users.estado', '1')
        ->where(function ($query) {
            $query->where('users.cargo', 'Director');
        })
        ->groupBy('ugel')
        ->get();
        // Luego, itera sobre los resultados para obtener los valores deseados
        $totalsDir = [];
        foreach ($totaldirByUgel as $total) {
            $totalsDir[$total->ugel] = $total->totaldocentes;
        }
    // Esta consulta es para el total de director de cada ugel

    // Esta consulta es para el total de docentes de cada ugel
        $totaldocByUgel = User::select(
            DB::raw('COUNT(DISTINCT users.id) as totaldocentes'),
            DB::raw('CASE 
                WHEN users.ugel = "Ugel Ambo" THEN "Ugel Ambo"
                WHEN users.ugel = "Ugel Huánuco" THEN "Ugel Huánuco"
                WHEN users.ugel = "Ugel Dos de Mayo" THEN "Ugel Dos de Mayo"
                WHEN users.ugel = "Ugel Huamalies" THEN "Ugel Huamalies"
                WHEN users.ugel = "Ugel Leoncio prado" THEN "Ugel Leoncio prado"
                WHEN users.ugel = "Ugel Pachitea" THEN "Ugel Pachitea"
                WHEN users.ugel = "Ugel Puerto Inca" THEN "Ugel Puerto Inca"
                WHEN users.ugel = "Ugel Yarowilca" THEN "Ugel Yarowilca"
                WHEN users.ugel = "Ugel Marañon" THEN "Ugel Marañon"
                WHEN users.ugel = "Ugel Lauricocha" THEN "Ugel Lauricocha"
                WHEN users.ugel = "Ugel Huacaybamba" THEN "Ugel Huacaybamba"
                ELSE "Otra UGEL"
            END as ugel')
        )
        ->where('users.estado', '1')
        ->where(function ($query) {
            $query->where('users.cargo', 'Docente');
        })
        ->groupBy('ugel')
        ->get();
        // Luego, itera sobre los resultados para obtener los valores deseados
        $totalsDoc = [];
        foreach ($totaldocByUgel as $total) {
            $totalsDoc[$total->ugel] = $total->totaldocentes;
        }
    // Esta consulta es para el total de docentes de cada ugel

    // Esta consulta es para el total de pc de cada ugel
        $totalpcByUgel = User::select(
            DB::raw('COUNT(DISTINCT users.id) as totaldocentes'),
            DB::raw('CASE 
                WHEN users.ugel = "Ugel Ambo" THEN "Ugel Ambo"
                WHEN users.ugel = "Ugel Huánuco" THEN "Ugel Huánuco"
                WHEN users.ugel = "Ugel Dos de Mayo" THEN "Ugel Dos de Mayo"
                WHEN users.ugel = "Ugel Huamalies" THEN "Ugel Huamalies"
                WHEN users.ugel = "Ugel Leoncio prado" THEN "Ugel Leoncio prado"
                WHEN users.ugel = "Ugel Pachitea" THEN "Ugel Pachitea"
                WHEN users.ugel = "Ugel Puerto Inca" THEN "Ugel Puerto Inca"
                WHEN users.ugel = "Ugel Yarowilca" THEN "Ugel Yarowilca"
                WHEN users.ugel = "Ugel Marañon" THEN "Ugel Marañon"
                WHEN users.ugel = "Ugel Lauricocha" THEN "Ugel Lauricocha"
                WHEN users.ugel = "Ugel Huacaybamba" THEN "Ugel Huacaybamba"
                ELSE "Otra UGEL"
            END as ugel')
        )
        ->where('users.estado', '1')
        ->where(function ($query) {
            $query->where('users.cargo', 'Profesor Coordinador');
        })
        ->groupBy('ugel')
        ->get();
        // Luego, itera sobre los resultados para obtener los valores deseados
        $totalsPc = [];
        foreach ($totalpcByUgel as $total) {
            $totalsPc[$total->ugel] = $total->totaldocentes;
        }
    // Esta consulta es para el total de pc de cada ugel

    // Accede a los valores deseados de la cantidad total de usuarios cada ugel por docente, pc y director
        $totaluserAmboCount = $totalsU['Ugel Ambo'] ?? 0;
        $totaluserHuanucoCount = $totalsU['Ugel Huánuco'] ?? 0;
        $totaluserDosdeMayoCount = $totalsU['Ugel Dos de Mayo'] ?? 0;
        $totaluserHuamaliesCount = $totalsU['Ugel Huamalies'] ?? 0;
        $totaluserPradoCount = $totalsU['Ugel Leoncio prado'] ?? 0;
        $totaluserPachiteaCount = $totalsU['Ugel Pachitea'] ?? 0;
        $totaluserIncaCount = $totalsU['Ugel Puerto Inca'] ?? 0;
        $totaluserYarowilcaCount = $totalsU['Ugel Yarowilca'] ?? 0;
        $totaluserMarañonCount = $totalsU['Ugel Marañon'] ?? 0;
        $totaluserLauricochaCount = $totalsU['Ugel Lauricocha'] ?? 0;
        $totaluserHuacaybambaCount = $totalsU['Ugel Huacaybamba'] ?? 0;

    // Accede a los valores deseados de la cantidad total de usuarios cada ugel por director
        $totaldirAmboCount = $totalsDir['Ugel Ambo'] ?? 0;
        $totaldirHuanucoCount = $totalsDir['Ugel Huánuco'] ?? 0;
        $totaldirDosdeMayoCount = $totalsDir['Ugel Dos de Mayo'] ?? 0;
        $totaldirHuamaliesCount = $totalsDir['Ugel Huamalies'] ?? 0;
        $totaldirPradoCount = $totalsDir['Ugel Leoncio prado'] ?? 0;
        $totaldirPachiteaCount = $totalsDir['Ugel Pachitea'] ?? 0;
        $totaldirIncaCount = $totalsDir['Ugel Puerto Inca'] ?? 0;
        $totaldirYarowilcaCount = $totalsDir['Ugel Yarowilca'] ?? 0;
        $totaldirMarañonCount = $totalsDir['Ugel Marañon'] ?? 0;
        $totaldirLauricochaCount = $totalsDir['Ugel Lauricocha'] ?? 0;
        $totaldirHuacaybambaCount = $totalsDir['Ugel Huacaybamba'] ?? 0;
    
    // Accede a los valores deseados de la cantidad total de usuarios cada ugel por docente
        $totaldocAmboCount = $totalsDoc['Ugel Ambo'] ?? 0;
        $totaldocHuanucoCount = $totalsDoc['Ugel Huánuco'] ?? 0;
        $totaldocDosdeMayoCount = $totalsDoc['Ugel Dos de Mayo'] ?? 0;
        $totaldocHuamaliesCount = $totalsDoc['Ugel Huamalies'] ?? 0;
        $totaldocPradoCount = $totalsDoc['Ugel Leoncio prado'] ?? 0;
        $totaldocPachiteaCount = $totalsDoc['Ugel Pachitea'] ?? 0;
        $totaldocIncaCount = $totalsDoc['Ugel Puerto Inca'] ?? 0;
        $totaldocYarowilcaCount = $totalsDoc['Ugel Yarowilca'] ?? 0;
        $totaldocMarañonCount = $totalsDoc['Ugel Marañon'] ?? 0;
        $totaldocLauricochaCount = $totalsDoc['Ugel Lauricocha'] ?? 0;
        $totaldocHuacaybambaCount = $totalsDoc['Ugel Huacaybamba'] ?? 0;

    // Accede a los valores deseados de la cantidad total de usuarios cada ugel por pc
        $totalpcAmboCount = $totalsPc['Ugel Ambo'] ?? 0;
        $totalpcHuanucoCount = $totalsPc['Ugel Huánuco'] ?? 0;
        $totalpcDosdeMayoCount = $totalsPc['Ugel Dos de Mayo'] ?? 0;
        $totalpcHuamaliesCount = $totalsPc['Ugel Huamalies'] ?? 0;
        $totalpcPradoCount = $totalsPc['Ugel Leoncio prado'] ?? 0;
        $totalpcPachiteaCount = $totalsPc['Ugel Pachitea'] ?? 0;
        $totalpcIncaCount = $totalsPc['Ugel Puerto Inca'] ?? 0;
        $totalpcYarowilcaCount = $totalsPc['Ugel Yarowilca'] ?? 0;
        $totalpcMarañonCount = $totalsPc['Ugel Marañon'] ?? 0;
        $totalpcLauricochaCount = $totalsPc['Ugel Lauricocha'] ?? 0;
        $totalpcHuacaybambaCount = $totalsPc['Ugel Huacaybamba'] ?? 0;
    
    // Esta consulta es para el total de instituciones, diectores y pofesor coordinador de cada ugel
        $totalinstitucionByUgel = Institucion::select(
            DB::raw('COUNT(DISTINCT institucions.id) as totalinstituciones'),
            DB::raw('CASE 
                WHEN institucions.ugel = "Ugel Ambo" THEN "Ugel Ambo"
                WHEN institucions.ugel = "Ugel Huánuco" THEN "Ugel Huánuco"
                WHEN institucions.ugel = "Ugel Dos de Mayo" THEN "Ugel Dos de Mayo"
                WHEN institucions.ugel = "Ugel Huamalies" THEN "Ugel Huamalies"
                WHEN institucions.ugel = "Ugel Leoncio prado" THEN "Ugel Leoncio prado"
                WHEN institucions.ugel = "Ugel Pachitea" THEN "Ugel Pachitea"
                WHEN institucions.ugel = "Ugel Puerto Inca" THEN "Ugel Puerto Inca"
                WHEN institucions.ugel = "Ugel Yarowilca" THEN "Ugel Yarowilca"
                WHEN institucions.ugel = "Ugel Marañon" THEN "Ugel Marañon"
                WHEN institucions.ugel = "Ugel Lauricocha" THEN "Ugel Lauricocha"
                WHEN institucions.ugel = "Ugel Huacaybamba" THEN "Ugel Huacaybamba"
                ELSE "Otra UGEL"
            END as ugel')
        )
        ->where('institucions.estado', '1')
        ->groupBy('ugel')
        ->get();
        // Luego, itera sobre los resultados para obtener los valores deseados
        $totalsI = [];
        foreach ($totalinstitucionByUgel as $total) {
            $totalsI[$total->ugel] = $total->totalinstituciones;
        }
 
    // Accede a los valores deseados de la cantidad total de instituciones cada ugel por docente, pc y director
        $totalinstitucionAmboCount = $totalsI['Ugel Ambo'] ?? 0;
        $totalinstitucionHuanucoCount = $totalsI['Ugel Huánuco'] ?? 0;
        $totalinstitucionDosdeMayoCount = $totalsI['Ugel Dos de Mayo'] ?? 0;
        $totalinstitucionHuamaliesCount = $totalsI['Ugel Huamalies'] ?? 0;
        $totalinstitucionPradoCount = $totalsI['Ugel Leoncio prado'] ?? 0;
        $totalinstitucionPachiteaCount = $totalsI['Ugel Pachitea'] ?? 0;
        $totalinstitucionIncaCount = $totalsI['Ugel Puerto Inca'] ?? 0;
        $totalinstitucionYarowilcaCount = $totalsI['Ugel Yarowilca'] ?? 0;
        $totalinstitucionMarañonCount = $totalsI['Ugel Marañon'] ?? 0;
        $totalinstitucionLauricochaCount = $totalsI['Ugel Lauricocha'] ?? 0;
        $totalinstitucionHuacaybambaCount = $totalsI['Ugel Huacaybamba'] ?? 0;

    // Esta consulta es para el total de acciones de cada ugel
        $totalaccionByUgel = Accion::select(
            DB::raw('COUNT(DISTINCT pro_accions.id) as totalacciones'),
            DB::raw('CASE 
                WHEN users.ugel = "Ugel Ambo" THEN "Ugel Ambo"
                WHEN users.ugel = "Ugel Huánuco" THEN "Ugel Huánuco"
                WHEN users.ugel = "Ugel Dos de Mayo" THEN "Ugel Dos de Mayo"
                WHEN users.ugel = "Ugel Huamalies" THEN "Ugel Huamalies"
                WHEN users.ugel = "Ugel Leoncio prado" THEN "Ugel Leoncio prado"
                WHEN users.ugel = "Ugel Pachitea" THEN "Ugel Pachitea"
                WHEN users.ugel = "Ugel Puerto Inca" THEN "Ugel Puerto Inca"
                WHEN users.ugel = "Ugel Yarowilca" THEN "Ugel Yarowilca"
                WHEN users.ugel = "Ugel Marañon" THEN "Ugel Marañon"
                WHEN users.ugel = "Ugel Lauricocha" THEN "Ugel Lauricocha"
                WHEN users.ugel = "Ugel Huacaybamba" THEN "Ugel Huacaybamba"
                ELSE "Otra UGEL"
            END as ugel')
        )
        ->join('users', 'pro_accions.idUser', '=', 'users.id')
        ->where('pro_accions.estado', '1')
        ->where('pro_accions.tipo', 'sensibilizacion')
        ->groupBy('ugel')
        ->get();
        // Luego, itera sobre los resultados para obtener los valores deseados
        $totalsAcc = [];
        foreach ($totalaccionByUgel as $total) {
            $totalsAcc[$total->ugel] = $total->totalacciones;
        }
    // Esta consulta es para el total de docentes que registraron acciones de cada ugel
        $totaldocXaccionByUgel = Accion::select(
            DB::raw('COUNT(DISTINCT pro_accions.idUser) as totaldocacciones'),
            DB::raw('CASE 
                WHEN users.ugel = "Ugel Ambo" THEN "Ugel Ambo"
                WHEN users.ugel = "Ugel Huánuco" THEN "Ugel Huánuco"
                WHEN users.ugel = "Ugel Dos de Mayo" THEN "Ugel Dos de Mayo"
                WHEN users.ugel = "Ugel Huamalies" THEN "Ugel Huamalies"
                WHEN users.ugel = "Ugel Leoncio prado" THEN "Ugel Leoncio prado"
                WHEN users.ugel = "Ugel Pachitea" THEN "Ugel Pachitea"
                WHEN users.ugel = "Ugel Puerto Inca" THEN "Ugel Puerto Inca"
                WHEN users.ugel = "Ugel Yarowilca" THEN "Ugel Yarowilca"
                WHEN users.ugel = "Ugel Marañon" THEN "Ugel Marañon"
                WHEN users.ugel = "Ugel Lauricocha" THEN "Ugel Lauricocha"
                WHEN users.ugel = "Ugel Huacaybamba" THEN "Ugel Huacaybamba"
                ELSE "Otra UGEL"
            END as ugel')
        )
        ->join('users', 'pro_accions.idUser', '=', 'users.id')
        ->where('pro_accions.estado', '1')
        ->where('pro_accions.tipo', 'sensibilizacion')
        ->groupBy('ugel')
        ->get();
        // Luego, itera sobre los resultados para obtener los valores deseados
        $totalsdocXAcc = [];
        foreach ($totaldocXaccionByUgel as $total) {
            $totalsdocXAcc[$total->ugel] = $total->totaldocacciones;
        }
    // Accede a los valores deseados de la canidad total de acciones de cada ugel por docente, pc y director
        $totalaccionAmboCount = $totalsAcc['Ugel Ambo'] ?? 0;
        $totalaccionHuanucoCount = $totalsAcc['Ugel Huánuco'] ?? 0;
        $totalaccionDosdeMayoCount = $totalsAcc['Ugel Dos de Mayo'] ?? 0;
        $totalaccionHuamaliesCount = $totalsAcc['Ugel Huamalies'] ?? 0;
        $totalaccionPradoCount = $totalsAcc['Ugel Leoncio prado'] ?? 0;
        $totalaccionPachiteaCount = $totalsAcc['Ugel Pachitea'] ?? 0;
        $totalaccionIncaCount = $totalsAcc['Ugel Puerto Inca'] ?? 0;
        $totalaccionYarowilcaCount = $totalsAcc['Ugel Yarowilca'] ?? 0;
        $totalaccionMarañonCount = $totalsAcc['Ugel Marañon'] ?? 0;
        $totalaccionLauricochaCount = $totalsAcc['Ugel Lauricocha'] ?? 0;
        $totalaccionHuacaybambaCount = $totalsAcc['Ugel Huacaybamba'] ?? 0;

    // Accede a los valores deseados de la canidad total de acciones de cada ugel por docente, pc y director

        $totaldocXaccionAmboCount = $totalsdocXAcc['Ugel Ambo'] ?? 0;
        $totaldocXaccionHuanucoCount = $totalsdocXAcc['Ugel Huánuco'] ?? 0;
        $totaldocXaccionDosdeMayoCount = $totalsdocXAcc['Ugel Dos de Mayo'] ?? 0;
        $totaldocXaccionHuamaliesCount = $totalsdocXAcc['Ugel Huamalies'] ?? 0;
        $totaldocXaccionPradoCount = $totalsdocXAcc['Ugel Leoncio prado'] ?? 0;
        $totaldocXaccionPachiteaCount = $totalsdocXAcc['Ugel Pachitea'] ?? 0;
        $totaldocXaccionIncaCount = $totalsdocXAcc['Ugel Puerto Inca'] ?? 0;
        $totaldocXaccionYarowilcaCount = $totalsdocXAcc['Ugel Yarowilca'] ?? 0;
        $totaldocXaccionMarañonCount = $totalsdocXAcc['Ugel Marañon'] ?? 0;
        $totaldocXaccionLauricochaCount = $totalsdocXAcc['Ugel Lauricocha'] ?? 0;
        $totaldocXaccionHuacaybambaCount = $totalsdocXAcc['Ugel Huacaybamba'] ?? 0;

    // Esta consulta es para el total de difusiones de cada ugel
        $totaldifucionByUgel = Accion::select(
            DB::raw('COUNT(DISTINCT pro_accions.id) as totaldifuciones'),
            DB::raw('CASE 
                WHEN users.ugel = "Ugel Ambo" THEN "Ugel Ambo"
                WHEN users.ugel = "Ugel Huánuco" THEN "Ugel Huánuco"
                WHEN users.ugel = "Ugel Dos de Mayo" THEN "Ugel Dos de Mayo"
                WHEN users.ugel = "Ugel Huamalies" THEN "Ugel Huamalies"
                WHEN users.ugel = "Ugel Leoncio prado" THEN "Ugel Leoncio prado"
                WHEN users.ugel = "Ugel Pachitea" THEN "Ugel Pachitea"
                WHEN users.ugel = "Ugel Puerto Inca" THEN "Ugel Puerto Inca"
                WHEN users.ugel = "Ugel Yarowilca" THEN "Ugel Yarowilca"
                WHEN users.ugel = "Ugel Marañon" THEN "Ugel Marañon"
                WHEN users.ugel = "Ugel Lauricocha" THEN "Ugel Lauricocha"
                WHEN users.ugel = "Ugel Huacaybamba" THEN "Ugel Huacaybamba"
                ELSE "Otra UGEL"
            END as ugel')
        )
        ->join('users', 'pro_accions.idUser', '=', 'users.id')
        ->where('pro_accions.estado', '1')
        ->where('pro_accions.tipo', 'difusion')
        ->groupBy('ugel')
        ->get();
        // Luego, itera sobre los resultados para obtener los valores deseados
        $totalsDif = [];
        foreach ($totaldifucionByUgel as $total) {
            $totalsDif[$total->ugel] = $total->totaldifuciones;
        }
    // Esta consulta es para el total de docentes que registraron difusiones de cada ugel
        $totaldocXdifucionByUgel = Accion::select(
            DB::raw('COUNT(DISTINCT pro_accions.idUser) as totaldocdifuciones'),
            DB::raw('CASE 
                WHEN users.ugel = "Ugel Ambo" THEN "Ugel Ambo"
                WHEN users.ugel = "Ugel Huánuco" THEN "Ugel Huánuco"
                WHEN users.ugel = "Ugel Dos de Mayo" THEN "Ugel Dos de Mayo"
                WHEN users.ugel = "Ugel Huamalies" THEN "Ugel Huamalies"
                WHEN users.ugel = "Ugel Leoncio prado" THEN "Ugel Leoncio prado"
                WHEN users.ugel = "Ugel Pachitea" THEN "Ugel Pachitea"
                WHEN users.ugel = "Ugel Puerto Inca" THEN "Ugel Puerto Inca"
                WHEN users.ugel = "Ugel Yarowilca" THEN "Ugel Yarowilca"
                WHEN users.ugel = "Ugel Marañon" THEN "Ugel Marañon"
                WHEN users.ugel = "Ugel Lauricocha" THEN "Ugel Lauricocha"
                WHEN users.ugel = "Ugel Huacaybamba" THEN "Ugel Huacaybamba"
                ELSE "Otra UGEL"
            END as ugel')
        )
        ->join('users', 'pro_accions.idUser', '=', 'users.id')
        ->where('pro_accions.estado', '1')
        ->where('pro_accions.tipo', 'difusion')
        ->groupBy('ugel')
        ->get();
        // Luego, itera sobre los resultados para obtener los valores deseados
        $totalsdocXDif = [];
        foreach ($totaldocXdifucionByUgel as $total) {
            $totalsdocXDif[$total->ugel] = $total->totaldocdifuciones;
        }
    // Accede a los valores deseados de la canidad total de difusiones de cada ugel 
        $totaldifucionAmboCount = $totalsDif['Ugel Ambo'] ?? 0;
        $totaldifucionHuanucoCount = $totalsDif['Ugel Huánuco'] ?? 0;
        $totaldifucionDosdeMayoCount = $totalsDif['Ugel Dos de Mayo'] ?? 0;
        $totaldifucionHuamaliesCount = $totalsDif['Ugel Huamalies'] ?? 0;
        $totaldifucionPradoCount = $totalsDif['Ugel Leoncio prado'] ?? 0;
        $totaldifucionPachiteaCount = $totalsDif['Ugel Pachitea'] ?? 0;
        $totaldifucionIncaCount = $totalsDif['Ugel Puerto Inca'] ?? 0;
        $totaldifucionYarowilcaCount = $totalsDif['Ugel Yarowilca'] ?? 0;
        $totaldifucionMarañonCount = $totalsDif['Ugel Marañon'] ?? 0;
        $totaldifucionLauricochaCount = $totalsDif['Ugel Lauricocha'] ?? 0;
        $totaldifucionHuacaybambaCount = $totalsDif['Ugel Huacaybamba'] ?? 0;

    // Accede a los valores deseados de la canidad total de difusiones de cada ugel por docente, pc y director
        
        $totaldocXdifucionAmboCount = $totalsdocXDif['Ugel Ambo'] ?? 0;
        $totaldocXdifucionHuanucoCount = $totalsdocXDif['Ugel Huánuco'] ?? 0;
        $totaldocXdifucionDosdeMayoCount = $totalsdocXDif['Ugel Dos de Mayo'] ?? 0;
        $totaldocXdifucionHuamaliesCount = $totalsdocXDif['Ugel Huamalies'] ?? 0;
        $totaldocXdifucionPradoCount = $totalsdocXDif['Ugel Leoncio prado'] ?? 0;
        $totaldocXdifucionPachiteaCount = $totalsdocXDif['Ugel Pachitea'] ?? 0;
        $totaldocXdifucionIncaCount = $totalsdocXDif['Ugel Puerto Inca'] ?? 0;
        $totaldocXdifucionYarowilcaCount = $totalsdocXDif['Ugel Yarowilca'] ?? 0;
        $totaldocXdifucionMarañonCount = $totalsdocXDif['Ugel Marañon'] ?? 0;
        $totaldocXdifucionLauricochaCount = $totalsdocXDif['Ugel Lauricocha'] ?? 0;
        $totaldocXdifucionHuacaybambaCount = $totalsdocXDif['Ugel Huacaybamba'] ?? 0;
    // Esta consulta es para el total de evidencias de cada ugel
        $totalevidenciaByUgel = Evidencia::select(
            DB::raw('COUNT(DISTINCT pro_evidencias.id) as totalevidencias'),
            DB::raw('CASE 
                WHEN users.ugel = "Ugel Ambo" THEN "Ugel Ambo"
                WHEN users.ugel = "Ugel Huánuco" THEN "Ugel Huánuco"
                WHEN users.ugel = "Ugel Dos de Mayo" THEN "Ugel Dos de Mayo"
                WHEN users.ugel = "Ugel Huamalies" THEN "Ugel Huamalies"
                WHEN users.ugel = "Ugel Leoncio prado" THEN "Ugel Leoncio prado"
                WHEN users.ugel = "Ugel Pachitea" THEN "Ugel Pachitea"
                WHEN users.ugel = "Ugel Puerto Inca" THEN "Ugel Puerto Inca"
                WHEN users.ugel = "Ugel Yarowilca" THEN "Ugel Yarowilca"
                WHEN users.ugel = "Ugel Marañon" THEN "Ugel Marañon"
                WHEN users.ugel = "Ugel Lauricocha" THEN "Ugel Lauricocha"
                WHEN users.ugel = "Ugel Huacaybamba" THEN "Ugel Huacaybamba"
                ELSE "Otra UGEL"
            END as ugel')
        )
        ->join('users', 'pro_evidencias.idUser', '=', 'users.id')
        ->where('pro_evidencias.estado', '1')
        ->groupBy('ugel')
        ->get();
        // Luego, itera sobre los resultados para obtener los valores deseados
        $totalsEvi = [];
        foreach ($totalevidenciaByUgel as $total) {
            $totalsEvi[$total->ugel] = $total->totalevidencias;
        }
    
    // Esta consulta es para el total de docentes que registraron evidencias de cada ugel
        $totaldocXevidenciaByUgel = Evidencia::select(
            DB::raw('COUNT(DISTINCT pro_evidencias.idUser) as totaldocevidencias'),
            DB::raw('CASE 
                WHEN users.ugel = "Ugel Ambo" THEN "Ugel Ambo"
                WHEN users.ugel = "Ugel Huánuco" THEN "Ugel Huánuco"
                WHEN users.ugel = "Ugel Dos de Mayo" THEN "Ugel Dos de Mayo"
                WHEN users.ugel = "Ugel Huamalies" THEN "Ugel Huamalies"
                WHEN users.ugel = "Ugel Leoncio prado" THEN "Ugel Leoncio prado"
                WHEN users.ugel = "Ugel Pachitea" THEN "Ugel Pachitea"
                WHEN users.ugel = "Ugel Puerto Inca" THEN "Ugel Puerto Inca"
                WHEN users.ugel = "Ugel Yarowilca" THEN "Ugel Yarowilca"
                WHEN users.ugel = "Ugel Marañon" THEN "Ugel Marañon"
                WHEN users.ugel = "Ugel Lauricocha" THEN "Ugel Lauricocha"
                WHEN users.ugel = "Ugel Huacaybamba" THEN "Ugel Huacaybamba"
                ELSE "Otra UGEL"
            END as ugel')
        )
        ->join('users', 'pro_evidencias.idUser', '=', 'users.id')
        ->where('pro_evidencias.estado', '1')
        ->groupBy('ugel')
        ->get();
        // Luego, itera sobre los resultados para obtener los valores deseados
        $totalsdocXEvi = [];
        foreach ($totaldocXevidenciaByUgel as $total) {
            $totalsdocXEvi[$total->ugel] = $total->totaldocevidencias;
        }
    
    // Accede a los valores deseados de la canidad total de evidencias de cada ugel 
        $totalevidenciaAmboCount = $totalsEvi['Ugel Ambo'] ?? 0;
        $totalevidenciaHuanucoCount = $totalsEvi['Ugel Huánuco'] ?? 0;
        $totalevidenciaDosdeMayoCount = $totalsEvi['Ugel Dos de Mayo'] ?? 0;
        $totalevidenciaHuamaliesCount = $totalsEvi['Ugel Huamalies'] ?? 0;
        $totalevidenciaPradoCount = $totalsEvi['Ugel Leoncio prado'] ?? 0;
        $totalevidenciaPachiteaCount = $totalsEvi['Ugel Pachitea'] ?? 0;
        $totalevidenciaIncaCount = $totalsEvi['Ugel Puerto Inca'] ?? 0;
        $totalevidenciaYarowilcaCount = $totalsEvi['Ugel Yarowilca'] ?? 0;
        $totalevidenciaMarañonCount = $totalsEvi['Ugel Marañon'] ?? 0;
        $totalevidenciaLauricochaCount = $totalsEvi['Ugel Lauricocha'] ?? 0;
        $totalevidenciaHuacaybambaCount = $totalsEvi['Ugel Huacaybamba'] ?? 0;
    // Accede a los valores deseados de la canidad total de evidencias de cada ugel por docente, pc y director
    
        $totaldocXevidenciaAmboCount = $totalsdocXEvi['Ugel Ambo'] ?? 0;
        $totaldocXevidenciaHuanucoCount = $totalsdocXEvi['Ugel Huánuco'] ?? 0;
        $totaldocXevidenciaDosdeMayoCount = $totalsdocXEvi['Ugel Dos de Mayo'] ?? 0;
        $totaldocXevidenciaHuamaliesCount = $totalsdocXEvi['Ugel Huamalies'] ?? 0;
        $totaldocXevidenciaPradoCount = $totalsdocXEvi['Ugel Leoncio prado'] ?? 0;
        $totaldocXevidenciaPachiteaCount = $totalsdocXEvi['Ugel Pachitea'] ?? 0;
        $totaldocXevidenciaIncaCount = $totalsdocXEvi['Ugel Puerto Inca'] ?? 0;
        $totaldocXevidenciaYarowilcaCount = $totalsdocXEvi['Ugel Yarowilca'] ?? 0;
        $totaldocXevidenciaMarañonCount = $totalsdocXEvi['Ugel Marañon'] ?? 0;
        $totaldocXevidenciaLauricochaCount = $totalsdocXEvi['Ugel Lauricocha'] ?? 0;
        $totaldocXevidenciaHuacaybambaCount = $totalsdocXEvi['Ugel Huacaybamba'] ?? 0;

    // Esta consulta es para el total de informes de cada ugel
        $totalinformeByUgel = Informe::select(
            DB::raw('COUNT(DISTINCT pro_informes.id) as totalinformes'),
            DB::raw('CASE 
                WHEN users.ugel = "Ugel Ambo" THEN "Ugel Ambo"
                WHEN users.ugel = "Ugel Huánuco" THEN "Ugel Huánuco"
                WHEN users.ugel = "Ugel Dos de Mayo" THEN "Ugel Dos de Mayo"
                WHEN users.ugel = "Ugel Huamalies" THEN "Ugel Huamalies"
                WHEN users.ugel = "Ugel Leoncio prado" THEN "Ugel Leoncio prado"
                WHEN users.ugel = "Ugel Pachitea" THEN "Ugel Pachitea"
                WHEN users.ugel = "Ugel Puerto Inca" THEN "Ugel Puerto Inca"
                WHEN users.ugel = "Ugel Yarowilca" THEN "Ugel Yarowilca"
                WHEN users.ugel = "Ugel Marañon" THEN "Ugel Marañon"
                WHEN users.ugel = "Ugel Lauricocha" THEN "Ugel Lauricocha"
                WHEN users.ugel = "Ugel Huacaybamba" THEN "Ugel Huacaybamba"
                ELSE "Otra UGEL"
            END as ugel')
        )
        ->join('users', 'pro_informes.idUser', '=', 'users.id')
        ->where('pro_informes.estado', '1')
        ->groupBy('ugel')
        ->get();
        // Luego, itera sobre los resultados para obtener los valores deseados
        $totalsInf = [];
        foreach ($totalinformeByUgel as $total) {
            $totalsInf[$total->ugel] = $total->totalinformes;
        }

    // Esta consulta es para el total de docentes que registraron informes de cada ugel
        $totaldocXinformeByUgel = Informe::select(
            DB::raw('COUNT(DISTINCT pro_informes.idUser) as totaldocinformes'),
            DB::raw('CASE 
                WHEN users.ugel = "Ugel Ambo" THEN "Ugel Ambo"
                WHEN users.ugel = "Ugel Huánuco" THEN "Ugel Huánuco"
                WHEN users.ugel = "Ugel Dos de Mayo" THEN "Ugel Dos de Mayo"
                WHEN users.ugel = "Ugel Huamalies" THEN "Ugel Huamalies"
                WHEN users.ugel = "Ugel Leoncio prado" THEN "Ugel Leoncio prado"
                WHEN users.ugel = "Ugel Pachitea" THEN "Ugel Pachitea"
                WHEN users.ugel = "Ugel Puerto Inca" THEN "Ugel Puerto Inca"
                WHEN users.ugel = "Ugel Yarowilca" THEN "Ugel Yarowilca"
                WHEN users.ugel = "Ugel Marañon" THEN "Ugel Marañon"
                WHEN users.ugel = "Ugel Lauricocha" THEN "Ugel Lauricocha"
                WHEN users.ugel = "Ugel Huacaybamba" THEN "Ugel Huacaybamba"
                ELSE "Otra UGEL"
            END as ugel')
        )
        ->join('users', 'pro_informes.idUser', '=', 'users.id')
        ->where('pro_informes.estado', '1')
        ->groupBy('ugel')
        ->get();
        // Luego, itera sobre los resultados para obtener los valores deseados
        $totalsdocXInf = [];
        foreach ($totaldocXinformeByUgel as $total) {
            $totalsdocXInf[$total->ugel] = $total->totaldocinformes;
        }

    // Accede a los valores deseados de la canidad total de informes de cada ugel 
        $totalinformeAmboCount = $totalsInf['Ugel Ambo'] ?? 0;
        $totalinformeHuanucoCount = $totalsInf['Ugel Huánuco'] ?? 0;
        $totalinformeDosdeMayoCount = $totalsInf['Ugel Dos de Mayo'] ?? 0;
        $totalinformeHuamaliesCount = $totalsInf['Ugel Huamalies'] ?? 0;
        $totalinformePradoCount = $totalsInf['Ugel Leoncio prado'] ?? 0;
        $totalinformePachiteaCount = $totalsInf['Ugel Pachitea'] ?? 0;
        $totalinformeIncaCount = $totalsInf['Ugel Puerto Inca'] ?? 0;
        $totalinformeYarowilcaCount = $totalsInf['Ugel Yarowilca'] ?? 0;
        $totalinformeMarañonCount = $totalsInf['Ugel Marañon'] ?? 0;
        $totalinformeLauricochaCount = $totalsInf['Ugel Lauricocha'] ?? 0;
        $totalinformeHuacaybambaCount = $totalsInf['Ugel Huacaybamba'] ?? 0;
    // Accede a los valores deseados de la canidad total de informes de cada ugel por docente, pc y director

        $totaldocXinformeAmboCount = $totalsdocXInf['Ugel Ambo'] ?? 0;
        $totaldocXinformeHuanucoCount = $totalsdocXInf['Ugel Huánuco'] ?? 0;
        $totaldocXinformeDosdeMayoCount = $totalsdocXInf['Ugel Dos de Mayo'] ?? 0;
        $totaldocXinformeHuamaliesCount = $totalsdocXInf['Ugel Huamalies'] ?? 0;
        $totaldocXinformePradoCount = $totalsdocXInf['Ugel Leoncio prado'] ?? 0;
        $totaldocXinformePachiteaCount = $totalsdocXInf['Ugel Pachitea'] ?? 0;
        $totaldocXinformeIncaCount = $totalsdocXInf['Ugel Puerto Inca'] ?? 0;
        $totaldocXinformeYarowilcaCount = $totalsdocXInf['Ugel Yarowilca'] ?? 0;
        $totaldocXinformeMarañonCount = $totalsdocXInf['Ugel Marañon'] ?? 0;
        $totaldocXinformeLauricochaCount = $totalsdocXInf['Ugel Lauricocha'] ?? 0;
        $totaldocXinformeHuacaybambaCount = $totalsdocXInf['Ugel Huacaybamba'] ?? 0;
    // Esta consulta es para el total de planes de cada ugel
        $totalplanByUgel = Plan::select(
            DB::raw('COUNT(DISTINCT pro_plans.id) as totalplanes'),
            DB::raw('CASE 
                WHEN users.ugel = "Ugel Ambo" THEN "Ugel Ambo"
                WHEN users.ugel = "Ugel Huánuco" THEN "Ugel Huánuco"
                WHEN users.ugel = "Ugel Dos de Mayo" THEN "Ugel Dos de Mayo"
                WHEN users.ugel = "Ugel Huamalies" THEN "Ugel Huamalies"
                WHEN users.ugel = "Ugel Leoncio prado" THEN "Ugel Leoncio prado"
                WHEN users.ugel = "Ugel Pachitea" THEN "Ugel Pachitea"
                WHEN users.ugel = "Ugel Puerto Inca" THEN "Ugel Puerto Inca"
                WHEN users.ugel = "Ugel Yarowilca" THEN "Ugel Yarowilca"
                WHEN users.ugel = "Ugel Marañon" THEN "Ugel Marañon"
                WHEN users.ugel = "Ugel Lauricocha" THEN "Ugel Lauricocha"
                WHEN users.ugel = "Ugel Huacaybamba" THEN "Ugel Huacaybamba"
                ELSE "Otra UGEL"
            END as ugel')
        )
        ->join('users', 'pro_plans.idUser', '=', 'users.id')
        ->where('pro_plans.estado', '1')
        ->groupBy('ugel')
        ->get();
        // Luego, itera sobre los resultados para obtener los valores deseados
        $totalsPla = [];
        foreach ($totalplanByUgel as $total) {
            $totalsPla[$total->ugel] = $total->totalplanes;
        }

    // Esta consulta es para el total de docentes que registraron planes de cada ugel
        $totaldocXplanByUgel = Plan::select(
            DB::raw('COUNT(DISTINCT pro_plans.idUser) as totaldocplanes'),
            DB::raw('CASE 
                WHEN users.ugel = "Ugel Ambo" THEN "Ugel Ambo"
                WHEN users.ugel = "Ugel Huánuco" THEN "Ugel Huánuco"
                WHEN users.ugel = "Ugel Dos de Mayo" THEN "Ugel Dos de Mayo"
                WHEN users.ugel = "Ugel Huamalies" THEN "Ugel Huamalies"
                WHEN users.ugel = "Ugel Leoncio prado" THEN "Ugel Leoncio prado"
                WHEN users.ugel = "Ugel Pachitea" THEN "Ugel Pachitea"
                WHEN users.ugel = "Ugel Puerto Inca" THEN "Ugel Puerto Inca"
                WHEN users.ugel = "Ugel Yarowilca" THEN "Ugel Yarowilca"
                WHEN users.ugel = "Ugel Marañon" THEN "Ugel Marañon"
                WHEN users.ugel = "Ugel Lauricocha" THEN "Ugel Lauricocha"
                WHEN users.ugel = "Ugel Huacaybamba" THEN "Ugel Huacaybamba"
                ELSE "Otra UGEL"
            END as ugel')
        )
        ->join('users', 'pro_plans.idUser', '=', 'users.id')
        ->where('pro_plans.estado', '1')
        ->groupBy('ugel')
        ->get();
        // Luego, itera sobre los resultados para obtener los valores deseados
        $totalsdocXPla = [];
        foreach ($totaldocXplanByUgel as $total) {
            $totalsdocXPla[$total->ugel] = $total->totaldocplanes;
        }

    // Accede a los valores deseados de la canidad total de planes de cada ugel 
        $totalplanAmboCount = $totalsPla['Ugel Ambo'] ?? 0;
        $totalplanHuanucoCount = $totalsPla['Ugel Huánuco'] ?? 0;
        $totalplanDosdeMayoCount = $totalsPla['Ugel Dos de Mayo'] ?? 0;
        $totalplanHuamaliesCount = $totalsPla['Ugel Huamalies'] ?? 0;
        $totalplanPradoCount = $totalsPla['Ugel Leoncio prado'] ?? 0;
        $totalplanPachiteaCount = $totalsPla['Ugel Pachitea'] ?? 0;
        $totalplanIncaCount = $totalsPla['Ugel Puerto Inca'] ?? 0;
        $totalplanYarowilcaCount = $totalsPla['Ugel Yarowilca'] ?? 0;
        $totalplanMarañonCount = $totalsPla['Ugel Marañon'] ?? 0;
        $totalplanLauricochaCount = $totalsPla['Ugel Lauricocha'] ?? 0;
        $totalplanHuacaybambaCount = $totalsPla['Ugel Huacaybamba'] ?? 0;
    // Accede a los valores deseados de la canidad total de planes de cada ugel por docente, pc y director

        $totaldocXplanAmboCount = $totalsdocXPla['Ugel Ambo'] ?? 0;
        $totaldocXplanHuanucoCount = $totalsdocXPla['Ugel Huánuco'] ?? 0;
        $totaldocXplanDosdeMayoCount = $totalsdocXPla['Ugel Dos de Mayo'] ?? 0;
        $totaldocXplanHuamaliesCount = $totalsdocXPla['Ugel Huamalies'] ?? 0;
        $totaldocXplanPradoCount = $totalsdocXPla['Ugel Leoncio prado'] ?? 0;
        $totaldocXplanPachiteaCount = $totalsdocXPla['Ugel Pachitea'] ?? 0;
        $totaldocXplanIncaCount = $totalsdocXPla['Ugel Puerto Inca'] ?? 0;
        $totaldocXplanYarowilcaCount = $totalsdocXPla['Ugel Yarowilca'] ?? 0;
        $totaldocXplanMarañonCount = $totalsdocXPla['Ugel Marañon'] ?? 0;
        $totaldocXplanLauricochaCount = $totalsdocXPla['Ugel Lauricocha'] ?? 0;
        $totaldocXplanHuacaybambaCount = $totalsdocXPla['Ugel Huacaybamba'] ?? 0;

    // Esta consulta es para el total de producciones de cada ugel
        $totalproduccionByUgel = Produccion::select(
            DB::raw('COUNT(DISTINCT pro_produccions.id) as totalproducciones'),
            DB::raw('CASE 
                WHEN users.ugel = "Ugel Ambo" THEN "Ugel Ambo"
                WHEN users.ugel = "Ugel Huánuco" THEN "Ugel Huánuco"
                WHEN users.ugel = "Ugel Dos de Mayo" THEN "Ugel Dos de Mayo"
                WHEN users.ugel = "Ugel Huamalies" THEN "Ugel Huamalies"
                WHEN users.ugel = "Ugel Leoncio prado" THEN "Ugel Leoncio prado"
                WHEN users.ugel = "Ugel Pachitea" THEN "Ugel Pachitea"
                WHEN users.ugel = "Ugel Puerto Inca" THEN "Ugel Puerto Inca"
                WHEN users.ugel = "Ugel Yarowilca" THEN "Ugel Yarowilca"
                WHEN users.ugel = "Ugel Marañon" THEN "Ugel Marañon"
                WHEN users.ugel = "Ugel Lauricocha" THEN "Ugel Lauricocha"
                WHEN users.ugel = "Ugel Huacaybamba" THEN "Ugel Huacaybamba"
                ELSE "Otra UGEL"
            END as ugel')
        )
        ->join('users', 'pro_produccions.idUser', '=', 'users.id')
        ->where('pro_produccions.estado', '1')
        ->groupBy('ugel')
        ->get();
        // Luego, itera sobre los resultados para obtener los valores deseados
        $totalsPro = [];
        foreach ($totalproduccionByUgel as $total) {
            $totalsPro[$total->ugel] = $total->totalproducciones;
        }

    // Esta consulta es para el total de docentes que registraron planes de cada ugel
        $totaldocXproduccionByUgel = Produccion::select(
            DB::raw('COUNT(DISTINCT pro_produccions.idUser) as totaldocproducciones'),
            DB::raw('CASE 
                WHEN users.ugel = "Ugel Ambo" THEN "Ugel Ambo"
                WHEN users.ugel = "Ugel Huánuco" THEN "Ugel Huánuco"
                WHEN users.ugel = "Ugel Dos de Mayo" THEN "Ugel Dos de Mayo"
                WHEN users.ugel = "Ugel Huamalies" THEN "Ugel Huamalies"
                WHEN users.ugel = "Ugel Leoncio prado" THEN "Ugel Leoncio prado"
                WHEN users.ugel = "Ugel Pachitea" THEN "Ugel Pachitea"
                WHEN users.ugel = "Ugel Puerto Inca" THEN "Ugel Puerto Inca"
                WHEN users.ugel = "Ugel Yarowilca" THEN "Ugel Yarowilca"
                WHEN users.ugel = "Ugel Marañon" THEN "Ugel Marañon"
                WHEN users.ugel = "Ugel Lauricocha" THEN "Ugel Lauricocha"
                WHEN users.ugel = "Ugel Huacaybamba" THEN "Ugel Huacaybamba"
                ELSE "Otra UGEL"
            END as ugel')
        )
        ->join('users', 'pro_produccions.idUser', '=', 'users.id')
        ->where('pro_produccions.estado', '1')
        ->groupBy('ugel')
        ->get();
        // Luego, itera sobre los resultados para obtener los valores deseados
        $totalsdocXPro = [];
        foreach ($totaldocXproduccionByUgel as $total) {
            $totalsdocXPro[$total->ugel] = $total->totaldocproducciones;
        }

    // Accede a los valores deseados de la canidad total de planes de cada ugel 
        $totalproduccionAmboCount = $totalsPro['Ugel Ambo'] ?? 0;
        $totalproduccionHuanucoCount = $totalsPro['Ugel Huánuco'] ?? 0;
        $totalproduccionDosdeMayoCount = $totalsPro['Ugel Dos de Mayo'] ?? 0;
        $totalproduccionHuamaliesCount = $totalsPro['Ugel Huamalies'] ?? 0;
        $totalproduccionPradoCount = $totalsPro['Ugel Leoncio prado'] ?? 0;
        $totalproduccionPachiteaCount = $totalsPro['Ugel Pachitea'] ?? 0;
        $totalproduccionIncaCount = $totalsPro['Ugel Puerto Inca'] ?? 0;
        $totalproduccionYarowilcaCount = $totalsPro['Ugel Yarowilca'] ?? 0;
        $totalproduccionMarañonCount = $totalsPro['Ugel Marañon'] ?? 0;
        $totalproduccionLauricochaCount = $totalsPro['Ugel Lauricocha'] ?? 0;
        $totalproduccionHuacaybambaCount = $totalsPro['Ugel Huacaybamba'] ?? 0;
    // Accede a los valores deseados de la canidad total de producciones de cada ugel por docente, pc y director

        $totaldocXproduccionAmboCount = $totalsdocXPro['Ugel Ambo'] ?? 0;
        $totaldocXproduccionHuanucoCount = $totalsdocXPro['Ugel Huánuco'] ?? 0;
        $totaldocXproduccionDosdeMayoCount = $totalsdocXPro['Ugel Dos de Mayo'] ?? 0;
        $totaldocXproduccionHuamaliesCount = $totalsdocXPro['Ugel Huamalies'] ?? 0;
        $totaldocXproduccionPradoCount = $totalsdocXPro['Ugel Leoncio prado'] ?? 0;
        $totaldocXproduccionPachiteaCount = $totalsdocXPro['Ugel Pachitea'] ?? 0;
        $totaldocXproduccionIncaCount = $totalsdocXPro['Ugel Puerto Inca'] ?? 0;
        $totaldocXproduccionYarowilcaCount = $totalsdocXPro['Ugel Yarowilca'] ?? 0;
        $totaldocXproduccionMarañonCount = $totalsdocXPro['Ugel Marañon'] ?? 0;
        $totaldocXproduccionLauricochaCount = $totalsdocXPro['Ugel Lauricocha'] ?? 0;
        $totaldocXproduccionHuacaybambaCount = $totalsdocXPro['Ugel Huacaybamba'] ?? 0;

    // Esta consulta es para el total de agendas de cada ugel
        $totalagendaByUgel = Agenda::select(
            DB::raw('COUNT(DISTINCT pro_agendas.id) as totalagendas'),
            DB::raw('CASE 
                WHEN users.ugel = "Ugel Ambo" THEN "Ugel Ambo"
                WHEN users.ugel = "Ugel Huánuco" THEN "Ugel Huánuco"
                WHEN users.ugel = "Ugel Dos de Mayo" THEN "Ugel Dos de Mayo"
                WHEN users.ugel = "Ugel Huamalies" THEN "Ugel Huamalies"
                WHEN users.ugel = "Ugel Leoncio prado" THEN "Ugel Leoncio prado"
                WHEN users.ugel = "Ugel Pachitea" THEN "Ugel Pachitea"
                WHEN users.ugel = "Ugel Puerto Inca" THEN "Ugel Puerto Inca"
                WHEN users.ugel = "Ugel Yarowilca" THEN "Ugel Yarowilca"
                WHEN users.ugel = "Ugel Marañon" THEN "Ugel Marañon"
                WHEN users.ugel = "Ugel Lauricocha" THEN "Ugel Lauricocha"
                WHEN users.ugel = "Ugel Huacaybamba" THEN "Ugel Huacaybamba"
                ELSE "Otra UGEL"
            END as ugel')
        )
        ->join('users', 'pro_agendas.idUser', '=', 'users.id')
        ->where('pro_agendas.estado', '1')
        ->groupBy('ugel')
        ->get();
        // Luego, itera sobre los resultados para obtener los valores deseados
        $totalsAge = [];
        foreach ($totalagendaByUgel as $total) {
            $totalsAge[$total->ugel] = $total->totalagendas;
        }

    // Esta consulta es para el total de docentes que registraron agendas de cada ugel
        $totaldocXagendaByUgel = Agenda::select(
            DB::raw('COUNT(DISTINCT pro_agendas.idUser) as totaldocagendas'),
            DB::raw('CASE 
                WHEN users.ugel = "Ugel Ambo" THEN "Ugel Ambo"
                WHEN users.ugel = "Ugel Huánuco" THEN "Ugel Huánuco"
                WHEN users.ugel = "Ugel Dos de Mayo" THEN "Ugel Dos de Mayo"
                WHEN users.ugel = "Ugel Huamalies" THEN "Ugel Huamalies"
                WHEN users.ugel = "Ugel Leoncio prado" THEN "Ugel Leoncio prado"
                WHEN users.ugel = "Ugel Pachitea" THEN "Ugel Pachitea"
                WHEN users.ugel = "Ugel Puerto Inca" THEN "Ugel Puerto Inca"
                WHEN users.ugel = "Ugel Yarowilca" THEN "Ugel Yarowilca"
                WHEN users.ugel = "Ugel Marañon" THEN "Ugel Marañon"
                WHEN users.ugel = "Ugel Lauricocha" THEN "Ugel Lauricocha"
                WHEN users.ugel = "Ugel Huacaybamba" THEN "Ugel Huacaybamba"
                ELSE "Otra UGEL"
            END as ugel')
        )
        ->join('users', 'pro_agendas.idUser', '=', 'users.id')
        ->where('pro_agendas.estado', '1')
        ->groupBy('ugel')
        ->get();
        // Luego, itera sobre los resultados para obtener los valores deseados
        $totalsdocXAge = [];
        foreach ($totaldocXagendaByUgel as $total) {
            $totalsdocXAge[$total->ugel] = $total->totaldocagendas;
        }

    // Accede a los valores deseados de la canidad total de agendas de cada ugel 
        $totalagendaAmboCount = $totalsAge['Ugel Ambo'] ?? 0;
        $totalagendaHuanucoCount = $totalsAge['Ugel Huánuco'] ?? 0;
        $totalagendaDosdeMayoCount = $totalsAge['Ugel Dos de Mayo'] ?? 0;
        $totalagendaHuamaliesCount = $totalsAge['Ugel Huamalies'] ?? 0;
        $totalagendaPradoCount = $totalsAge['Ugel Leoncio prado'] ?? 0;
        $totalagendaPachiteaCount = $totalsAge['Ugel Pachitea'] ?? 0;
        $totalagendaIncaCount = $totalsAge['Ugel Puerto Inca'] ?? 0;
        $totalagendaYarowilcaCount = $totalsAge['Ugel Yarowilca'] ?? 0;
        $totalagendaMarañonCount = $totalsAge['Ugel Marañon'] ?? 0;
        $totalagendaLauricochaCount = $totalsAge['Ugel Lauricocha'] ?? 0;
        $totalagendaHuacaybambaCount = $totalsAge['Ugel Huacaybamba'] ?? 0;
    // Accede a los valores deseados de la canidad total de agendas de cada ugel por docente, pc y director

        $totaldocXagendaAmboCount = $totalsdocXAge['Ugel Ambo'] ?? 0;
        $totaldocXagendaHuanucoCount = $totalsdocXAge['Ugel Huánuco'] ?? 0;
        $totaldocXagendaDosdeMayoCount = $totalsdocXAge['Ugel Dos de Mayo'] ?? 0;
        $totaldocXagendaHuamaliesCount = $totalsdocXAge['Ugel Huamalies'] ?? 0;
        $totaldocXagendaPradoCount = $totalsdocXAge['Ugel Leoncio prado'] ?? 0;
        $totaldocXagendaPachiteaCount = $totalsdocXAge['Ugel Pachitea'] ?? 0;
        $totaldocXagendaIncaCount = $totalsdocXAge['Ugel Puerto Inca'] ?? 0;
        $totaldocXagendaYarowilcaCount = $totalsdocXAge['Ugel Yarowilca'] ?? 0;
        $totaldocXagendaMarañonCount = $totalsdocXAge['Ugel Marañon'] ?? 0;
        $totaldocXagendaLauricochaCount = $totalsdocXAge['Ugel Lauricocha'] ?? 0;
        $totaldocXagendaHuacaybambaCount = $totalsdocXAge['Ugel Huacaybamba'] ?? 0;


    return view('dashboard.index', compact('data', 'totaluserAmboCount', 'totaluserDosdeMayoCount', 'totaluserHuanucoCount', 'totaluserHuamaliesCount',
                                             'totaluserPradoCount', 'totaluserPachiteaCount', 'totaluserIncaCount', 'totaluserYarowilcaCount',
                                             'totaluserMarañonCount', 'totaluserLauricochaCount', 'totaluserHuacaybambaCount'
                                             , 'totaldirAmboCount', 'totaldirDosdeMayoCount', 'totaldirHuanucoCount', 'totaldirHuamaliesCount',
                                             'totaldirPradoCount', 'totaldirPachiteaCount', 'totaldirIncaCount', 'totaldirYarowilcaCount',
                                             'totaldirMarañonCount', 'totaldirLauricochaCount', 'totaldirHuacaybambaCount'
                                             , 'totaldocAmboCount', 'totaldocDosdeMayoCount', 'totaldocHuanucoCount', 'totaldocHuamaliesCount',
                                             'totaldocPradoCount', 'totaldocPachiteaCount', 'totaldocIncaCount', 'totaldocYarowilcaCount',
                                             'totaldocMarañonCount', 'totaldocLauricochaCount', 'totaldocHuacaybambaCount'
                                             , 'totalpcAmboCount', 'totalpcDosdeMayoCount', 'totalpcHuanucoCount', 'totalpcHuamaliesCount',
                                             'totalpcPradoCount', 'totalpcPachiteaCount', 'totalpcIncaCount', 'totalpcYarowilcaCount',
                                             'totalpcMarañonCount', 'totalpcLauricochaCount', 'totalpcHuacaybambaCount'

                                             , 'totalinstitucionAmboCount', 'totalinstitucionDosdeMayoCount', 'totalinstitucionHuanucoCount', 'totalinstitucionHuamaliesCount',
                                             'totalinstitucionPradoCount', 'totalinstitucionPachiteaCount', 'totalinstitucionIncaCount', 'totalinstitucionYarowilcaCount',
                                             'totalinstitucionMarañonCount', 'totalinstitucionLauricochaCount', 'totalinstitucionHuacaybambaCount'
                                            
                                             , 'totalaccionAmboCount', 'totalaccionDosdeMayoCount', 'totalaccionHuanucoCount', 'totalaccionHuamaliesCount',
                                             'totalaccionPradoCount', 'totalaccionPachiteaCount', 'totalaccionIncaCount', 'totalaccionYarowilcaCount',
                                             'totalaccionMarañonCount', 'totalaccionLauricochaCount', 'totalaccionHuacaybambaCount'
                                             , 'totaldocXaccionAmboCount', 'totaldocXaccionDosdeMayoCount', 'totaldocXaccionHuanucoCount', 'totaldocXaccionHuamaliesCount',
                                             'totaldocXaccionPradoCount', 'totaldocXaccionPachiteaCount', 'totaldocXaccionIncaCount', 'totaldocXaccionYarowilcaCount',
                                             'totaldocXaccionMarañonCount', 'totaldocXaccionLauricochaCount', 'totaldocXaccionHuacaybambaCount'
                                            
                                             , 'totaldifucionAmboCount', 'totaldifucionDosdeMayoCount', 'totaldifucionHuanucoCount', 'totaldifucionHuamaliesCount',
                                             'totaldifucionPradoCount', 'totaldifucionPachiteaCount', 'totaldifucionIncaCount', 'totaldifucionYarowilcaCount',
                                             'totaldifucionMarañonCount', 'totaldifucionLauricochaCount', 'totaldifucionHuacaybambaCount'
                                             , 'totaldocXdifucionAmboCount', 'totaldocXdifucionDosdeMayoCount', 'totaldocXdifucionHuanucoCount', 'totaldocXdifucionHuamaliesCount',
                                             'totaldocXdifucionPradoCount', 'totaldocXdifucionPachiteaCount', 'totaldocXdifucionIncaCount', 'totaldocXdifucionYarowilcaCount',
                                             'totaldocXdifucionMarañonCount', 'totaldocXdifucionLauricochaCount', 'totaldocXdifucionHuacaybambaCount'
                                            
                                             , 'totalevidenciaAmboCount', 'totalevidenciaDosdeMayoCount', 'totalevidenciaHuanucoCount', 'totalevidenciaHuamaliesCount',
                                             'totalevidenciaPradoCount', 'totalevidenciaPachiteaCount', 'totalevidenciaIncaCount', 'totalevidenciaYarowilcaCount',
                                             'totalevidenciaMarañonCount', 'totalevidenciaLauricochaCount', 'totalevidenciaHuacaybambaCount'
                                             , 'totaldocXevidenciaAmboCount', 'totaldocXevidenciaDosdeMayoCount', 'totaldocXevidenciaHuanucoCount', 'totaldocXevidenciaHuamaliesCount',
                                             'totaldocXevidenciaPradoCount', 'totaldocXevidenciaPachiteaCount', 'totaldocXevidenciaIncaCount', 'totaldocXevidenciaYarowilcaCount',
                                             'totaldocXevidenciaMarañonCount', 'totaldocXevidenciaLauricochaCount', 'totaldocXevidenciaHuacaybambaCount'
                                            
                                             , 'totalinformeAmboCount', 'totalinformeDosdeMayoCount', 'totalinformeHuanucoCount', 'totalinformeHuamaliesCount',
                                             'totalinformePradoCount', 'totalinformePachiteaCount', 'totalinformeIncaCount', 'totalinformeYarowilcaCount',
                                             'totalinformeMarañonCount', 'totalinformeLauricochaCount', 'totalinformeHuacaybambaCount'
                                             , 'totaldocXinformeAmboCount', 'totaldocXinformeDosdeMayoCount', 'totaldocXinformeHuanucoCount', 'totaldocXinformeHuamaliesCount',
                                             'totaldocXinformePradoCount', 'totaldocXinformePachiteaCount', 'totaldocXinformeIncaCount', 'totaldocXinformeYarowilcaCount',
                                             'totaldocXinformeMarañonCount', 'totaldocXinformeLauricochaCount', 'totaldocXinformeHuacaybambaCount'
                                            
                                             , 'totalplanAmboCount', 'totalplanDosdeMayoCount', 'totalplanHuanucoCount', 'totalplanHuamaliesCount',
                                             'totalplanPradoCount', 'totalplanPachiteaCount', 'totalplanIncaCount', 'totalplanYarowilcaCount',
                                             'totalplanMarañonCount', 'totalplanLauricochaCount', 'totalplanHuacaybambaCount'
                                             , 'totaldocXplanAmboCount', 'totaldocXplanDosdeMayoCount', 'totaldocXplanHuanucoCount', 'totaldocXplanHuamaliesCount',
                                             'totaldocXplanPradoCount', 'totaldocXplanPachiteaCount', 'totaldocXplanIncaCount', 'totaldocXplanYarowilcaCount',
                                             'totaldocXplanMarañonCount', 'totaldocXplanLauricochaCount', 'totaldocXplanHuacaybambaCount'
                                            
                                             , 'totalproduccionAmboCount', 'totalproduccionDosdeMayoCount', 'totalproduccionHuanucoCount', 'totalproduccionHuamaliesCount',
                                             'totalproduccionPradoCount', 'totalproduccionPachiteaCount', 'totalproduccionIncaCount', 'totalproduccionYarowilcaCount',
                                             'totalproduccionMarañonCount', 'totalproduccionLauricochaCount', 'totalproduccionHuacaybambaCount'
                                             , 'totaldocXproduccionAmboCount', 'totaldocXproduccionDosdeMayoCount', 'totaldocXproduccionHuanucoCount', 'totaldocXproduccionHuamaliesCount',
                                             'totaldocXproduccionPradoCount', 'totaldocXproduccionPachiteaCount', 'totaldocXproduccionIncaCount', 'totaldocXproduccionYarowilcaCount',
                                             'totaldocXproduccionMarañonCount', 'totaldocXproduccionLauricochaCount', 'totaldocXproduccionHuacaybambaCount'
                                            
                                             , 'totalagendaAmboCount', 'totalagendaDosdeMayoCount', 'totalagendaHuanucoCount', 'totalagendaHuamaliesCount',
                                             'totalagendaPradoCount', 'totalagendaPachiteaCount', 'totalagendaIncaCount', 'totalagendaYarowilcaCount',
                                             'totalagendaMarañonCount', 'totalagendaLauricochaCount', 'totalagendaHuacaybambaCount'
                                             , 'totaldocXagendaAmboCount', 'totaldocXagendaDosdeMayoCount', 'totaldocXagendaHuanucoCount', 'totaldocXagendaHuamaliesCount',
                                             'totaldocXagendaPradoCount', 'totaldocXagendaPachiteaCount', 'totaldocXagendaIncaCount', 'totaldocXagendaYarowilcaCount',
                                             'totaldocXagendaMarañonCount', 'totaldocXagendaLauricochaCount', 'totaldocXagendaHuacaybambaCount'));
        /*
        $docentesEnAgendas = DB::table('pro_agendas')->count();

        // Consulta para obtener la cantidad de docentes en evidencias
        $docentesEnEvidencias = DB::table('pro_evidencias')->count();

        // Consulta para obtener la cantidad de docentes en informes
        $docentesEnInformes = DB::table('pro_informes')->count();

        return view('dashboard.index', compact('docentesEnAgendas', 'docentesEnEvidencias', 'docentesEnInformes'));*/
    }
    public function ugel()
    {
        return "ugel";        
    }
    public function director()
    {
        return "director";        
    }
    public function pc()
    {
        return "pc";        
    }

    public function exportData(Request $request, $tipo){

        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        /*
        $cb_Docente = $request->has('checkbox_docente');
        $cb_Directo = $request->has('checkbox_directo');
        $cb_PC = $request->has('checkbox_pc');  */

        return Excel::download(new DataExport($start_date, $end_date, $tipo), 'registro.xlsx');
    }
    

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }
    
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}


class DataExport implements FromCollection{
    use Exportable;

    protected $start_date;
    protected $end_date;
    protected $tipo;

    public function __construct($start_date, $end_date, $tipo) {
        $this->start_date = $start_date;
        $this->end_date = $end_date;
        $this->tipo = $tipo;
    }

    function collection() {
        switch ($this->tipo) {
            case 'usuario':
                return User::select('name', 'email', 'dni', 'cargo', 'institucion', 'ugel')->where('estado', 1)->get();
            break;

            case 'institucion':
                return Institucion::select('nomInstitucion', 'codModular', 'nivel', 'centropoblado', 'ugel')->where('estado', 1)->get();
            break;
            

            case 'accion':
                return Accion::select('pro_accions.nombreAccion', 'pro_accions.descripcion', 'pro_accions.fecha', 'users.name' ,'users.cargo', 'users.institucion')
                            ->join('users','users.id','=','pro_accions.idUser')
                            ->where('pro_accions.tipo', 'sensibilizacion')
                            ->where('pro_accions.estado', 1)
                            ->whereBetween('pro_accions.fecha', [$this->start_date, $this->end_date])->get();
            break;

            case 'difusion':
                return Accion::select('pro_accions.nombreAccion', 'pro_accions.descripcion', 'pro_accions.fecha', 'users.name' ,'users.cargo', 'users.institucion')
                            ->join('users','users.id','=','pro_accions.idUser')
                            ->where('pro_accions.tipo', 'difusion')
                            ->where('pro_accions.estado', 1)
                            ->whereBetween('pro_accions.fecha', [$this->start_date, $this->end_date])->get();
            break;

            case 'agenda':
                return Agenda::where(function ($query) {
                    $query->where('start', '>=', $this->start_date)
                        ->where('start', '<=', $this->end_date);
                })->orWhere(function ($query) {
                    $query->where('end', '>=', $this->start_date)
                        ->where('end', '<=', $this->end_date);
                })->get();
            break;

            case 'evidencia':
                return Evidencia::select('pro_evidencias.nombreEvidencia', 'pro_evidencias.enlace', 'pro_evidencias.fecha', 'users.name' ,'users.cargo', 'users.institucion')
                            ->join('users','users.id','=','pro_evidencias.idUser')
                            ->where('pro_evidencias.estado', 1)
                            ->whereBetween('fecha', [$this->start_date, $this->end_date])->get();
            break;

            case 'informe':
                return Informe::select('pro_informes.nombreInforme', 'pro_informes.enlace', 'pro_informes.descripcion', 'pro_informes.created_at', 'users.name' ,'users.cargo', 'users.institucion')
                            ->join('users','users.id','=','pro_informes.idUser')
                            ->where('pro_informes.estado', 1)->get();
            break;

            case 'plan':
                return Plan::select('pro_plans.nombrePlan', 'pro_plans.enlace', 'pro_plans.descripcion', 'pro_plans.created_at', 'users.name' ,'users.cargo', 'users.institucion')
                            ->join('users','users.id','=','pro_plans.idUser')
                            ->where('pro_plans.estado', 1)->get();
            break;

            case 'produccion':
                return Produccion::select('pro_produccions.nombreProduccion', 'pro_produccions.enlace', 'pro_produccions.descripcion', 'pro_produccions.created_at', 'users.name' ,'users.cargo', 'users.institucion')
                            ->join('users','users.id','=','pro_produccions.idUser')
                            ->where('pro_produccions.estado', 1)->get();
            break;


            default:                
            break;
        }
        
    }
}
