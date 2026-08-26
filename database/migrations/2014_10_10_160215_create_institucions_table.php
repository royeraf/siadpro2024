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
        Schema::create('institucions', function (Blueprint $table) {
            $table->id();
            $table->string('nomInstitucion');
            $table->string('codModular',9);
            $table->string('nivel',40);
            $table->string('centropoblado',60);
            $table->string('provincia',40);
            $table->string('distrito',40);
            $table->string('ugel',50);
            $table->boolean('estado');
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
        Schema::dropIfExists('insticucions');
    }
};
