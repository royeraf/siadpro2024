<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Añade institucions.ugel_id (FK → ugeles) y hace backfill desde
     * institucions.ugel. NO se elimina la columna `ugel` todavía: se
     * conserva como legacy hasta migrar todo el código que la lee.
     */
    public function up(): void
    {
        Schema::table('institucions', function (Blueprint $table) {
            $table->foreignId('ugel_id')->nullable()->after('ugel')
                ->constrained('ugeles')->nullOnDelete();
        });

        $normalize = $this->normalizer();

        // Mapa clave-normalizada → id de ugel.
        $ugelPorClave = [];
        foreach (DB::table('ugeles')->get(['id', 'nombre']) as $ugel) {
            $ugelPorClave[$normalize($ugel->nombre)] = $ugel->id;
        }

        // Backfill por cada valor distinto presente en institucions.ugel.
        $valores = DB::table('institucions')
            ->whereNotNull('ugel')
            ->where('ugel', '!=', '')
            ->distinct()
            ->pluck('ugel');

        foreach ($valores as $valor) {
            $id = $ugelPorClave[$normalize($valor)] ?? null;
            if ($id) {
                DB::table('institucions')->where('ugel', $valor)->update(['ugel_id' => $id]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('institucions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('ugel_id');
        });
    }

    /**
     * Normaliza un nombre de UGEL a una clave comparable:
     * mayúsculas, sin tildes, sin prefijo "UGEL", espacios colapsados.
     */
    private function normalizer(): callable
    {
        return static function (?string $v): ?string {
            $v = trim((string) $v);
            if ($v === '') {
                return null;
            }
            $acentos = [
                'á'=>'A','à'=>'A','â'=>'A','ã'=>'A','ä'=>'A',
                'é'=>'E','è'=>'E','ê'=>'E','ë'=>'E',
                'í'=>'I','ì'=>'I','î'=>'I','ï'=>'I',
                'ó'=>'O','ò'=>'O','ô'=>'O','õ'=>'O','ö'=>'O',
                'ú'=>'U','ù'=>'U','û'=>'U','ü'=>'U',
                'ñ'=>'N',
                'Á'=>'A','À'=>'A','Â'=>'A','Ã'=>'A','Ä'=>'A',
                'É'=>'E','È'=>'E','Ê'=>'E','Ë'=>'E',
                'Í'=>'I','Ì'=>'I','Î'=>'I','Ï'=>'I',
                'Ó'=>'O','Ò'=>'O','Ô'=>'O','Õ'=>'O','Ö'=>'O',
                'Ú'=>'U','Ù'=>'U','Û'=>'U','Ü'=>'U',
                'Ñ'=>'N',
            ];
            $v = strtr(strtoupper($v), $acentos);
            $v = preg_replace('/^UGEL\s+/', '', $v);
            $v = preg_replace('/\s+/', ' ', $v);

            return trim($v);
        };
    }
};
