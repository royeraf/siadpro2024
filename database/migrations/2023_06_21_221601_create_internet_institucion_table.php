<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('internet_institucionesH', function (Blueprint $table) {
            $table->id();

            $table->string('usuario');

            $table->string('codigoModular');
            $table->string('nombreInstitucion');
            $table->string('nivelModalidad');
            $table->string('departamento');

            $table->string('provincia');
            $table->string('distrito');
            $table->string('centroPoblado');
            $table->string('ugel');

            $table->string('proveedorServicio');
            $table->string('megasContratadas');
            $table->string('costoMensual');
            $table->string('costoAnual');
            $table->string('tipoLinea');
            $table->string('coordenadaX');
            $table->string('coordenadaY');
            $table->string('fechaInstalacion');
            $table->string('inicioContrato');
            $table->string('finalContrato');

            $table->string('tipoDocumento');
            $table->string('nmrNombreResolucion');
            $table->string('descripcionResolucion');
            $table->string('archivoPDF')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('internet_instituciones');
    }
};
