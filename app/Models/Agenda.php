<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agenda extends Model
{
    use HasFactory;

    protected $table = 'pro_agendas';

    public $timestamps = true;

    /**
     * Catálogo semántico de secciones y sus códigos de color para la interfaz visual.
     */
    public const SECCIONES = [
        'lila'         => ['label' => 'Lila',         'hex' => '#FF0085'],
        'azul_oscuro'  => ['label' => 'Azul oscuro',  'hex' => '#0071C5'],
        'turquesa'     => ['label' => 'Turquesa',     'hex' => '#40E0D0'],
        'verde'        => ['label' => 'Verde',        'hex' => '#008000'],
        'amarillo'     => ['label' => 'Amarillo',     'hex' => '#FFD700'],
        'naranja'      => ['label' => 'Naranja',      'hex' => '#FF8C00'],
        'rojo'         => ['label' => 'Rojo',         'hex' => '#FF0000'],
        'violeta'      => ['label' => 'Violeta',      'hex' => '#9D00FF'],
        'marron'       => ['label' => 'Marrón',       'hex' => '#BA4A00'],
        'gris'         => ['label' => 'Gris',         'hex' => '#99A3A4'],
        'acero'        => ['label' => 'Acero',        'hex' => '#21618C'],
        'negro'        => ['label' => 'Negro',        'hex' => '#000000'],
        'azul_cielo'   => ['label' => 'Azul cielo',   'hex' => '#1E90FF'],
        'rojo_naranja' => ['label' => 'Rojo naranja', 'hex' => '#FF4500'],
        'verde_claro'  => ['label' => 'Verde claro',  'hex' => '#00FF7F'],
    ];

    /**
     * Mapeo de códigos hexadecimales a identificadores de sección (para compatibilidad).
     */
    public const HEX_A_SECCION = [
        '#FF0085' => 'lila',
        '#0071C5' => 'azul_oscuro',
        '#0071c5' => 'azul_oscuro',
        '#40E0D0' => 'turquesa',
        '#008000' => 'verde',
        '#FFD700' => 'amarillo',
        '#FF8C00' => 'naranja',
        '#FF0000' => 'rojo',
        '#9D00FF' => 'violeta',
        '#BA4A00' => 'marron',
        '#99A3A4' => 'gris',
        '#21618C' => 'acero',
        '#000000' => 'negro',
        '#000'    => 'negro',
        '#1E90FF' => 'azul_cielo',
        '#FF4500' => 'rojo_naranja',
        '#00FF7F' => 'verde_claro',
    ];

    protected $fillable = [
        'title',
        'evento',
        'seccion',
        'color',
        'start',
        'end',
        'idUser',
        'estado',
        'ugel',
    ];

    protected $appends = [
        'nomDocente',
        'institucion',
        'color',
        'seccion',
        'nombre_color',
        'nombre_seccion',
    ];

    /**
     * Mutador para 'color': normaliza si llega un código hexadecimal o el slug directo ('lila', 'verde').
     */
    public function setColorAttribute($value)
    {
        $valUpper = strtoupper((string) $value);
        $this->attributes['color'] = self::HEX_A_SECCION[$valUpper] ?? (self::HEX_A_SECCION[$value] ?? strtolower((string) $value));
    }

    /**
     * Mutador de compatibilidad para 'seccion': guarda el valor normalizado en la columna 'color'.
     */
    public function setSeccionAttribute($value)
    {
        $this->setColorAttribute($value);
    }

    /**
     * Accesor dinámico para 'color' (requerido por FullCalendar para pintar el evento):
     * resuelve dinámicamente el código hexadecimal (#HEX) a partir del identificador guardado en 'color'.
     */
    public function getColorAttribute(): string
    {
        $slug = strtolower((string) ($this->attributes['color'] ?? 'lila'));
        if (str_starts_with($slug, '#')) {
            return $slug;
        }
        return self::SECCIONES[$slug]['hex'] ?? '#FF0085';
    }

    /**
     * Accesor para 'seccion': retorna el identificador semántico del evento ('lila', 'verde', etc.).
     */
    public function getSeccionAttribute(): string
    {
        return strtolower((string) ($this->attributes['color'] ?? 'lila'));
    }

    /**
     * Accesor para el nombre legible del color/sección (ej. 'Lila', 'Azul oscuro', 'Verde').
     */
    public function getNombreColorAttribute(): string
    {
        $slug = strtolower((string) ($this->attributes['color'] ?? ''));
        if (str_starts_with($slug, '#')) {
            $slug = self::HEX_A_SECCION[strtoupper($slug)] ?? $slug;
        }
        return self::SECCIONES[$slug]['label'] ?? ucfirst($slug ?: 'Sin sección');
    }

    /**
     * Alias de compatibilidad con 'nombre_seccion'.
     */
    public function getNombreSeccionAttribute(): string
    {
        return $this->getNombreColorAttribute();
    }

    /**
     * Relación con el usuario (docente) que registró la agenda.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'idUser');
    }

    /**
     * Alias de compatibilidad con la convención de otros modelos del sistema (Accion, Plan, Evidencia).
     */
    public function getUser()
    {
        return $this->hasOne(User::class, 'id', 'idUser');
    }

    /**
     * Accesor para nomDocente: obtiene dinámicamente el nombre desde la tabla users.
     */
    public function getNomDocenteAttribute($value = null)
    {
        return $this->user ? $this->user->name : $value;
    }

    /**
     * Accesor para institucion: obtiene dinámicamente la institución desde la tabla users.
     */
    public function getInstitucionAttribute($value = null)
    {
        return $this->user ? $this->user->institucion : $value;
    }
}
