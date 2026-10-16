<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgendaDia extends Model
{
    protected $table = 'pro_agenda_dias';

    public $timestamps = true;

    protected $fillable = [
        'agenda_id',
        'fecha',
        'hora_inicio',
        'hora_fin',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    /**
     * Agenda a la que pertenece el día programado.
     */
    public function agenda()
    {
        return $this->belongsTo(Agenda::class, 'agenda_id');
    }

    /**
     * Archivos (evidencias) subidos a este día.
     */
    public function evidencias()
    {
        return $this->hasMany(AgendaEvidencia::class, 'agenda_dia_id');
    }
}
