<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agenda extends Model
{
    use HasFactory;

    protected $table = 'pro_agendas';

    public $timestamps = true;

    protected $fillable = [
        'title',
        'evento',
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
    ];

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
     * Accesor para nomDocente: obtiene dinámicamente el nombre desde la tabla users (a través de la relación)
     * o respeta el alias si ya viene seleccionado en la consulta (ej. users.name as nomDocente).
     */
    public function getNomDocenteAttribute($value = null)
    {
        return $this->user ? $this->user->name : $value;
    }

    /**
     * Accesor para institucion: obtiene dinámicamente la institución desde la tabla users (a través de la relación)
     * o respeta el alias si ya viene seleccionado en la consulta (ej. users.institucion as institucion).
     */
    public function getInstitucionAttribute($value = null)
    {
        return $this->user ? $this->user->institucion : $value;
    }
}
