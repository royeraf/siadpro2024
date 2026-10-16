<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Institucion extends Model
{
    use HasFactory;
    protected $table = 'institucions';
    public $timestamps = true;

    protected $fillable = [
        'nomInstitucion',
        'codModular',
        'nivel',
        'centropoblado',
        'provincia',
        'distrito',
        'ugel',
        'ugel_id',
        'estado',
    ];

    public function ugel()
    {
        return $this->belongsTo(Ugel::class, 'ugel_id');
    }

    public function usuarios()
    {
        return $this->hasMany(User::class, 'institucion_id');
    }
}
