<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Institucion;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('instituciones/{codModular}', function ($codModular) {
    $codModular = trim($codModular);
    $results = Institucion::where('codModular', $codModular)
        ->orWhere('codModular', str_pad($codModular, 7, '0', STR_PAD_LEFT))
        ->orWhere('codModular', ltrim($codModular, '0'))
        ->get();

    return response()->json($results);
});