<?php

namespace App\Console\Commands;

use App\Models\Institucion;
use App\Models\Ugel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportarInstitucionesInicialCommand extends Command
{
    protected $signature = 'instituciones:importar-inicial
        {archivo? : Ruta del archivo .xlsx del reporte MINEDU}
        {--dry-run : Solo muestra el diff (pendientes), sin insertar nada}';

    protected $description = 'Importa instituciones educativas de nivel Inicial (A1/A2/A3/A5) pendientes en la BD';

    /** Niveles iniciales MINEDU a importar. */
    private const NIVELES_INICIAL = ['A1', 'A2', 'A3', 'A5'];

    public function handle(): int
    {
        $archivo = $this->argument('archivo')
            ?? '/home/royer/Downloads/rptDatosInstitucionesEducativas_20260826152840.xlsx';

        if (! is_file($archivo)) {
            $this->error("No se encontró el archivo: {$archivo}");

            return self::FAILURE;
        }

        $this->info('Leyendo el reporte MINEDU: ' . $archivo);

        $filas = $this->leerFilasInicial($archivo);

        $this->newLine();
        $this->line('Filas iniciales detectadas (A1/A2/A3/A5): ' . count($filas));

        if (! $filas) {
            $this->warn('No se encontraron registros iniciales.');

            return self::SUCCESS;
        }

        $existentes = $this->codigosExistentes();
        $pendientes = [];

        foreach ($filas as $fila) {
            $cod = self::normalizarCodModular($fila['codModular']);
            if ($cod === null || isset($existentes[$cod])) {
                continue;
            }
            $pendientes[] = $fila;
        }

        $this->line('Coincidencias (ya en BD): ' . (count($filas) - count($pendientes)));
        $this->line('Pendientes a importar: ' . count($pendientes));

        $this->imprimirResumen($pendientes);

        if ($this->option('dry-run')) {
            $this->newLine();
            $this->comment('Modo --dry-run: no se insertó ningún registro.');

            return self::SUCCESS;
        }

        if (! $pendientes) {
            $this->newLine();
            $this->info('No hay instituciones pendientes que importar.');

            return self::SUCCESS;
        }

        $insertados = $this->insertarPendientes($pendientes);

        $this->newLine();
        $this->info("Importación finalizada: {$insertados} instituciones insertadas.");

        return self::SUCCESS;
    }

    /**
     * Lee la hoja del reporte y devuelve solo las filas de nivel inicial,
     * con las columnas normalizadas (texto recortado).
     *
     * @return array<int, array{codModular:string,nomInstitucion:string,nivel:string,centropoblado:string,provincia:string,distrito:string,ugel:string}>
     */
    private function leerFilasInicial(string $archivo): array
    {
        $spreadsheet = IOFactory::load($archivo);
        $worksheet = $spreadsheet->getSheetByName('rptDatosInstitucionesEducativas')
            ?? $spreadsheet->getActiveSheet();

        $filas = [];

        foreach ($worksheet->getRowIterator(9) as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);
            $cells = [];
            foreach ($cellIterator as $cell) {
                $cells[$cell->getColumn()] = trim((string) $cell->getValue());
            }

            $nivel = $cells['J'] ?? '';
            if (! self::esNivelInicial($nivel)) {
                continue;
            }

            $codModular = $cells['G'] ?? '';
            if ($codModular === '' || self::normalizarCodModular($codModular) === null) {
                continue;
            }

            $filas[] = [
                'codModular' => $codModular,
                'nomInstitucion' => $cells['I'] ?? '',
                'nivel' => $nivel,
                'centropoblado' => $cells['F'] ?? '',
                'provincia' => $cells['D'] ?? '',
                'distrito' => $cells['E'] ?? '',
                'ugel' => $cells['B'] ?? '',
            ];
        }

        return $filas;
    }

    private static function esNivelInicial(string $nivel): bool
    {
        $nivel = ltrim($nivel);

        foreach (self::NIVELES_INICIAL as $prefijo) {
            if (str_starts_with($nivel, $prefijo)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Normaliza un código modular a 7 dígitos con ceros a la izquierda.
     * Devuelve null si no contiene dígitos.
     */
    private static function normalizarCodModular(string $cod): ?string
    {
        $digitos = preg_replace('/\D/', '', $cod);
        if ($digitos === '' || $digitos === null) {
            return null;
        }

        return str_pad($digitos, 7, '0', STR_PAD_LEFT);
    }

    /**
     * Códigos modulares ya presentes en la BD, indexados por su forma normalizada.
     *
     * @return array<string, true>
     */
    private function codigosExistentes(): array
    {
        $mapa = [];
        foreach (Institucion::query()->pluck('codModular') as $cod) {
            $normalizado = self::normalizarCodModular((string) $cod);
            if ($normalizado !== null) {
                $mapa[$normalizado] = true;
            }
        }

        return $mapa;
    }

    private function imprimirResumen(array $pendientes): void
    {
        if (! $pendientes) {
            return;
        }

        $porNivel = [];
        $porUgel = [];
        foreach ($pendientes as $fila) {
            $porNivel[$fila['nivel']] = ($porNivel[$fila['nivel']] ?? 0) + 1;
            $porUgel[$fila['ugel']] = ($porUgel[$fila['ugel']] ?? 0) + 1;
        }

        $this->newLine();
        $this->line('Pendientes por nivel:');
        foreach ($porNivel as $nivel => $n) {
            $this->line("  · {$nivel}: {$n}");
        }

        $this->line('Pendientes por UGEL:');
        foreach ($porUgel as $ugel => $n) {
            $this->line("  · {$ugel}: {$n}");
        }
    }

    /**
     * Inserta los pendientes dentro de una transacción. Devuelve la cantidad insertada.
     */
    private function insertarPendientes(array $pendientes): int
    {
        // Catálogo canónico de UGEL: clave normalizada -> nombre canónico.
        $ugelPorClave = [];
        foreach (Ugel::query()->get(['id', 'nombre']) as $ugel) {
            $ugelPorClave[Ugel::normalizarClave($ugel->nombre)] = $ugel->nombre;
        }

        $insertados = 0;

        DB::transaction(function () use ($pendientes, $ugelPorClave, &$insertados) {
            foreach ($pendientes as $fila) {
                $claveUgel = Ugel::normalizarClave($fila['ugel']);
                $nombreUgel = $ugelPorClave[$claveUgel] ?? Str::upper($fila['ugel']);
                $ugelId = Ugel::resolverId($fila['ugel']);

                Institucion::create([
                    'nomInstitucion' => Str::upper($fila['nomInstitucion']),
                    'codModular' => self::normalizarCodModular($fila['codModular']),
                    'nivel' => $fila['nivel'],
                    'centropoblado' => Str::upper($fila['centropoblado']),
                    'provincia' => Str::upper($fila['provincia']),
                    'distrito' => Str::upper($fila['distrito']),
                    'ugel' => $nombreUgel,
                    'ugel_id' => $ugelId,
                    'estado' => true,
                ]);

                $insertados++;
            }
        });

        return $insertados;
    }
}
