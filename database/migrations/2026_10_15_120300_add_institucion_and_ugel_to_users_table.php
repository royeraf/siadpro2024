<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Añade users.institucion_id (FK → institucions) y users.ugel_id (FK → ugeles)
     * y hace backfill desde los campos de texto legacy, que se conservan por ahora.
     *
     * Estrategia de emparejamiento de institución:
     *   1. Coincidencia exacta por nomInstitucion (case-insensitive).
     *   2. Coincidencia por codModular (exacta o sin ceros a la izquierda).
     *   3. Sin coincidencia → NULL (se reporta aparte; el texto original se conserva).
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('institucion_id')->nullable()->after('nivelinstitucion')
                ->constrained('institucions')->nullOnDelete();
            $table->foreignId('ugel_id')->nullable()->after('institucion_id')
                ->constrained('ugeles')->nullOnDelete();
        });

        $normalize = $this->normalizer();

        // 1) Backfill users.ugel_id desde users.ugel.
        $ugelPorClave = [];
        foreach (DB::table('ugeles')->get(['id', 'nombre']) as $ugel) {
            $ugelPorClave[$normalize($ugel->nombre)] = $ugel->id;
        }

        $valoresUgel = DB::table('users')
            ->whereNotNull('ugel')
            ->where('ugel', '!=', '')
            ->distinct()
            ->pluck('ugel');

        foreach ($valoresUgel as $valor) {
            $id = $ugelPorClave[$normalize($valor)] ?? null;
            if ($id) {
                DB::table('users')->where('ugel', $valor)->update(['ugel_id' => $id]);
            }
        }

        // 2) Backfill users.institucion_id desde users.institucion.
        $porNombre = [];
        $porCod = [];
        foreach (DB::table('institucions')->get(['id', 'nomInstitucion', 'codModular']) as $ins) {
            $claveNombre = strtoupper(trim((string) $ins->nomInstitucion));
            if ($claveNombre !== '' && ! isset($porNombre[$claveNombre])) {
                $porNombre[$claveNombre] = $ins->id;
            }
            $cod = trim((string) $ins->codModular);
            if ($cod !== '' && ! isset($porCod[$cod])) {
                $porCod[$cod] = $ins->id;
            }
        }

        $usuarios = DB::table('users')
            ->whereNotNull('institucion')
            ->where('institucion', '!=', '')
            ->get(['id', 'institucion']);

        foreach ($usuarios as $usuario) {
            $texto = trim((string) $usuario->institucion);
            if ($texto === '') {
                continue;
            }

            $id = $porNombre[strtoupper($texto)] ?? null;

            if (! $id) {
                $id = $porCod[$texto] ?? ($porCod[ltrim($texto, '0')] ?? null);
            }

            if ($id) {
                DB::table('users')->where('id', $usuario->id)->update(['institucion_id' => $id]);
            }
        }

        // 3) Derivar ugel_id desde la institución cuando users.ugel no resolvió.
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
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('ugel_id');
            $table->dropConstrainedForeignId('institucion_id');
        });
    }

    /**
     * Normaliza un nombre de UGEL a una clave comparable.
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
