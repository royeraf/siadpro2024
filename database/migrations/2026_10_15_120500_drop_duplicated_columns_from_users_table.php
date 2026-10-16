<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Elimina las 5 columnas duplicadas de `users` que ya se derivan de las
     * relaciones institucionRel()/ugelRel() (institucion_id/ugel_id).
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['institucion', 'nivelinstitucion', 'ugel', 'provincia', 'distrito']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('provincia', 80)->nullable()->after('estado');
            $table->string('distrito', 80)->nullable()->after('provincia');
            $table->string('institucion')->nullable()->after('distrito');
            $table->string('nivelinstitucion')->nullable()->after('institucion');
            $table->string('ugel')->nullable()->after('nivelinstitucion');
        });
    }
};
