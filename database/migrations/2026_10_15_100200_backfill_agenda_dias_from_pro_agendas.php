<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Backfill de `pro_agenda_dias` a partir de los `start`/`end` heredados de
 * `pro_agendas`.
 *
 * Contexto de los datos reales (base local == VPS, ~31.6k agendas activas):
 *   - `start`/`end` son varchar con dos formatos mezclados: "YYYY-MM-DD HH:MM:SS"
 *     y el de `<input type="datetime-local">`, "YYYY-MM-DDTHH:MM".
 *   - ~13.5k son "todo el día" (hora 00:00:00 en ambos) y ~12.9k de ellas usan
 *     el patrón de FullCalendar `inicio día D 00:00 -> fin D+1 00:00` con `end`
 *     EXCLUSIVO (hay que descontar un día o se crea un día fantasma).
 *   - Hay 5 registros con años corruptos (0024, 92024, 82026): se omiten.
 *
 * Las horas se dejan VACÍAS (NULL): el rango de hora por día es un concepto
 * nuevo que el dato histórico no tiene. Solo se conserva la fecha.
 */
return new class extends Migration
{
    public function up()
    {
        DB::table('pro_agendas')
            ->where('estado', 1)
            ->orderBy('id')
            ->chunkById(500, function ($agendas) {
                $now = now();
                $rows = [];

                foreach ($agendas as $agenda) {
                    foreach ($this->fechasDeAgenda($agenda->start, $agenda->end) as $fecha) {
                        $rows[] = [
                            'agenda_id'   => $agenda->id,
                            'fecha'       => $fecha,
                            'hora_inicio' => null,
                            'hora_fin'    => null,
                            'created_at'  => $now,
                            'updated_at'  => $now,
                        ];
                    }
                }

                if (! empty($rows)) {
                    // Idempotente gracias al único (agenda_id, fecha).
                    DB::table('pro_agenda_dias')->insertOrIgnore($rows);
                }
            });
    }

    public function down()
    {
        // Migración de datos: el `down` de la migración que crea la tabla ya la
        // elimina entera. No se revierte fila a fila.
    }

    /**
     * Fechas (Y-m-d) que abarca una agenda según sus `start`/`end` heredados.
     *
     * @return string[]
     */
    private function fechasDeAgenda(?string $start, ?string $end): array
    {
        $inicio = $this->parse($start);
        $fin    = $this->parse($end);

        if ($inicio === null || $fin === null) {
            return [];
        }

        $desde = Carbon::parse($inicio['fecha'])->startOfDay();
        $hasta = Carbon::parse($fin['fecha'])->startOfDay();

        $todoElDia = $inicio['hora'] === '00:00:00' && $fin['hora'] === '00:00:00';

        // Hay registros con el rango invertido (fin anterior al inicio, p. ej.
        // 2024-07-24 -> 2024-07-23); se ordenan en vez de descartarlos.
        if ($hasta->lt($desde)) {
            [$desde, $hasta] = [$hasta, $desde];
        }

        // `end` exclusivo de los eventos "todo el día" de FullCalendar.
        if (! $desde->equalTo($hasta) && $todoElDia) {
            $hasta->subDay();
        }

        if ($hasta->lt($desde)) {
            return [];
        }

        // Tope de seguridad ante rangos corruptos.
        if ((int) $desde->diffInDays($hasta) > 366) {
            return [];
        }

        $fechas = [];
        for ($d = $desde->copy(); $d->lte($hasta); $d->addDay()) {
            $fechas[] = $d->toDateString();
        }

        return $fechas;
    }

    /**
     * Extrae fecha y hora de un valor varchar heredado. Devuelve null si no
     * tiene un formato reconocible o el año es implausible.
     *
     * @return array{fecha: string, hora: string}|null
     */
    private function parse(?string $value): ?array
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        if (! preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})(?:[ T](\d{2}):(\d{2})(?::(\d{2}))?)?/', trim($value), $m)) {
            return null;
        }

        $anio = (int) $m[1];
        $mes  = (int) $m[2];
        $dia  = (int) $m[3];

        // Hay valores mal formados (p. ej. "2023-31-08") que el regex acepta
        // como sintaxis pero no son fechas reales.
        if (! checkdate($mes, $dia, $anio)) {
            return null;
        }

        if ($anio < 2010 || $anio > (int) date('Y') + 1) {
            return null;
        }

        $hora = '00:00:00';
        if (isset($m[4])) {
            $hh = (int) $m[4];
            $mm = (int) $m[5];
            if ($hh > 23 || $mm > 59) {
                return null;
            }
            $hora = sprintf('%02d:%02d:00', $hh, $mm);
        }

        return [
            'fecha' => sprintf('%04d-%02d-%02d', $anio, $mes, $dia),
            'hora'  => $hora,
        ];
    }
};
