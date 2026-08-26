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
        Schema::create('pro_plans', function (Blueprint $table) {
            $table->id();
            $table->string('nombrePlan');
            $table->string('documento')->nullable();
            $table->string('color')->nullable();
            $table->string('direccion')->nullable();
            $table->string('enlace')->nullable();
            $table->string('descripcion')->nullable();
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
        Schema::dropIfExists('plans');
    }
};
