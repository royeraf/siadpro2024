<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'dni',
        'cargo',
        'estado',
        'institucion_id',
        'ugel_id',
        'created_by',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
        // Campos legacy derivados de institucionRel()/ugelRel(). Se exponen en
        // la serialización (JSON de Inertia) para no romper las vistas/Vue que
        // leen user.institucion / user.ugel / user.provincia / user.distrito /
        // user.nivelinstitucion. Las consultas que listan usuarios cargan ambas
        // relaciones con eager loading para evitar N+1.
        'institucion',
        'nivelinstitucion',
        'ugel',
        'provincia',
        'distrito',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function agendas()
    {
        return $this->hasMany(Agenda::class, 'idUser');
    }

    public function institucionRel()
    {
        return $this->belongsTo(Institucion::class, 'institucion_id');
    }

    public function ugelRel()
    {
        return $this->belongsTo(Ugel::class, 'ugel_id');
    }

    /*
     |--------------------------------------------------------------------------
     | Accessors de compatibilidad (columnas legacy eliminadas de `users`)
     |--------------------------------------------------------------------------
     | Los campos institucion / nivelinstitucion / ugel / provincia / distrito
     | se eliminaron de `users` y ahora se derivan de las relaciones
     | institucionRel() / ugelRel(). Mantienen la API de atributos intacta
     | para el código y las vistas que aún los leen.
     */

    public function getInstitucionAttribute(): ?string
    {
        return $this->institucionRel?->nomInstitucion;
    }

    public function getNivelinstitucionAttribute(): ?string
    {
        $nivel = $this->institucionRel?->nivel;

        if (! $nivel) {
            return null;
        }

        $nivel = mb_strtolower($nivel);

        if (str_contains($nivel, 'no escolariz') || str_contains($nivel, 'pronoei')) {
            return 'No escolarizado - PRONOEI';
        }

        return 'Escolarizado';
    }

    public function getUgelAttribute(): ?string
    {
        return $this->ugelRel?->nombre;
    }

    public function getProvinciaAttribute(): ?string
    {
        return $this->institucionRel?->provincia ?? $this->ugelRel?->provincia;
    }

    public function getDistritoAttribute(): ?string
    {
        return $this->institucionRel?->distrito ?? $this->ugelRel?->distrito;
    }
}
