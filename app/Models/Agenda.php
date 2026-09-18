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
        'institucion',
        'title',
        'evento',
        'color',
        'start',
        'end',
        'idUser',
        'estado',
        'ugel',
        'nomDocente',
    ];

    /**
     * Evento al guardar para asegurar que nomDocente siempre referencie
     * los datos del docente desde la tabla users.
     */
    protected static function booted()
    {
        static::saving(function ($agenda) {
            if ($agenda->idUser && (empty($agenda->nomDocente) || $agenda->isDirty('idUser'))) {
                $user = $agenda->user ?: User::find($agenda->idUser);
                if ($user) {
                    $agenda->nomDocente = $user->name;
                }
            }
        });
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
     * Accesor para nomDocente: si tiene valor lo devuelve; si no,
     * lo obtiene directamente de la relación con users.
     */
    public function getNomDocenteAttribute($value = null)
    {
        if (!empty($value)) {
            return $value;
        }

        return $this->user ? $this->user->name : null;
    }
}
