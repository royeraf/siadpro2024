<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Catálogo de UGEL de la DRE Huánuco (nombre canónico, provincia y distrito sede).
     */
    private const UGELES = [
        ['nombre' => 'UGEL HUÁNUCO',        'provincia' => 'HUÁNUCO',        'distrito' => 'HUÁNUCO'],
        ['nombre' => 'UGEL AMBO',           'provincia' => 'AMBO',           'distrito' => 'AMBO'],
        ['nombre' => 'UGEL DOS DE MAYO',    'provincia' => 'DOS DE MAYO',    'distrito' => 'LA UNIÓN'],
        ['nombre' => 'UGEL LAURICOCHA',     'provincia' => 'LAURICOCHA',     'distrito' => 'JESÚS'],
        ['nombre' => 'UGEL LEONCIO PRADO',  'provincia' => 'LEONCIO PRADO',  'distrito' => 'RUPA-RUPA'],
        ['nombre' => 'UGEL HUACAYBAMBA',    'provincia' => 'HUACAYBAMBA',    'distrito' => 'HUACAYBAMBA'],
        ['nombre' => 'UGEL HUAMALÍES',      'provincia' => 'HUAMALÍES',      'distrito' => 'LLATA'],
        ['nombre' => 'UGEL MARAÑÓN',        'provincia' => 'MARAÑÓN',        'distrito' => 'HUACRACHUCO'],
        ['nombre' => 'UGEL PACHITEA',       'provincia' => 'PACHITEA',       'distrito' => 'PANAO'],
        ['nombre' => 'UGEL PUERTO INCA',    'provincia' => 'PUERTO INCA',    'distrito' => 'PUERTO INCA'],
        ['nombre' => 'UGEL YAROWILCA',      'provincia' => 'YAROWILCA',      'distrito' => 'CHAVINILLO'],
    ];

    public function up(): void
    {
        Schema::create('ugeles', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100)->unique();
            $table->string('provincia', 80)->nullable();
            $table->string('distrito', 80)->nullable();
            $table->boolean('estado')->default(true);
            $table->timestamps();
        });

        $now = now();
        foreach (self::UGELES as $ugel) {
            DB::table('ugeles')->insertOrIgnore(array_merge($ugel, [
                'estado' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ugeles');
    }
};
