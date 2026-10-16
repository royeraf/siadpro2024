<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgendaEvidencia extends Model
{
    protected $table = 'pro_agenda_evidencias';

    public $timestamps = true;

    protected $fillable = [
        'agenda_dia_id',
        'enlace',
        'nombre_original',
        'idUser',
        'estado',
    ];

    protected $appends = [
        'documento',
        'color',
    ];

    /**
     * Icono FontAwesome según la extensión del archivo. Reutiliza el mapa de
     * Evidencia para no duplicar la tabla de tipos soportados.
     */
    public function getDocumentoAttribute($value): string
    {
        return Evidencia::getInfoArchivo($this->enlace)['icono'] ?? Evidencia::DEFAULT_ARCHIVO['icono'];
    }

    /**
     * Color de presentación del icono según la extensión.
     */
    public function getColorAttribute($value): string
    {
        return Evidencia::getInfoArchivo($this->enlace)['color'] ?? Evidencia::DEFAULT_ARCHIVO['color'];
    }

    /**
     * Día programado al que corresponde el archivo.
     */
    public function dia()
    {
        return $this->belongsTo(AgendaDia::class, 'agenda_dia_id');
    }

    /**
     * Usuario que subió el archivo.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'idUser');
    }
}
