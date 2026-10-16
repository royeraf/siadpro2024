<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Repara el backfill de ugel_id para los valores que no coincidieron
     * por el manejo de acentos en minúscula (p. ej. "UGEL Huánuco",
     * "UGEL Huamalíes"). Reutiliza la normalización corregida.
     */
    public function up(): void
    {
        $normalize = $this->normalizer();

        $ugelPorClave = [];
        foreach (DB::table('ugeles')->get(['id', 'nombre']) as $ugel) {
            $ugelPorClave[$normalize($ugel->nombre)] = $ugel->id;
        }

        // Re-backfill de institucions que quedaron sin ugel_id.
        $valores = DB::table('institucions')
            ->whereNull('ugel_id')
            ->whereNotNull('ugel')
            ->where('ugel', '!=', '')
            ->distinct()
            ->pluck('ugel');

        foreach ($valores as $valor) {
            $id = $ugelPorClave[$normalize($valor)] ?? null;
            if ($id) {
                DB::table('institucions')
                    ->whereNull('ugel_id')
                    ->where('ugel', $valor)
                    ->update(['ugel_id' => $id]);
            }
        }

        // Re-derivar users.ugel_id desde la institución para los que quedaron NULL.
        DB::statement(
            'UPDATE users u
             JOIN institucions i ON i.id = u.institucion_id
             SET u.ugel_id = i.ugel_id
             WHERE u.ugel_id IS NULL
               AND u.institucion_id IS NOT NULL
               AND i.ugel_id IS NOT NULL'
        );
    }

    public function down(): void
    {
        // Operación de datos sin reversión significativa.
    }

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
