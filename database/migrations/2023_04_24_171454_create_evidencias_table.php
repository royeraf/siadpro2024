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
        Schema::create('pro_evidencias', function (Blueprint $table) {
            $table->id();
            $table->string('nombreEvidencia');
            $table->string('documento')->nullable();
            $table->string('color')->nullable();
            $table->string('enlace')->nullable();
            $table->string('enlacedrive')->nullable();
            $table->string('descripcion')->nullable();
            $table->date('fecha')->nullable();
            $table->unsignedBigInteger('idUser');
            $table->boolean('estado');
            $table->foreign('idUser')->references('id')->on('users');
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
        Schema::dropIfExists('evidencias');
    }
};
