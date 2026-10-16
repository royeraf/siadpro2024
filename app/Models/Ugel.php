<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ugel extends Model
{
    use HasFactory;

    protected $table = 'ugeles';

    protected $fillable = [
        'nombre',
        'provincia',
        'distrito',
        'estado',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];

    public function instituciones()
    {
        return $this->hasMany(Institucion::class, 'ugel_id');
    }

    public function usuarios()
    {
        return $this->hasMany(User::class, 'ugel_id');
    }

    /**
     * Normaliza un nombre de UGEL a una clave comparable:
     * mayúsculas, sin tildes, sin prefijo "UGEL", espacios colapsados.
     */
    public static function normalizarClave(?string $nombre): ?string
    {
        $v = trim((string) $nombre);
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
    }

    /**
     * Resuelve el id de una UGEL a partir de un nombre (o texto libre),
     * comparando por clave normalizada. Devuelve null si no hay coincidencia.
     */
    public static function resolverId(?string $nombre): ?int
    {
        $clave = self::normalizarClave($nombre);
        if ($clave === null) {
            return null;
        }

        foreach (self::all(['id', 'nombre']) as $ugel) {
            if (self::normalizarClave($ugel->nombre) === $clave) {
                return $ugel->id;
            }
        }

        return null;
    }
}
