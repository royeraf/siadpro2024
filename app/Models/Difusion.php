<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Difusion extends Model
{
    use HasFactory;

    protected $table = 'pro_difusions';
    public $timestamps = true;

    protected $fillable = [
        'nombreAccion',
        'lugar',
        'enlace',
        'descripcion',
        'fecha',
        'idUser',
        'estado',
    ];

    protected $appends = [
        'documento',
        'color',
    ];

    /**
     * Mapeo de extensiones a clases de icono (FontAwesome) y colores de presentación.
     */
    public const MAPA_ARCHIVOS = [
        'pdf' => [
            'icono' => 'fas fa-file-pdf',
            'color' => 'red',
        ],
        'doc' => [
            'icono' => 'fas fa-file-word',
            'color' => 'blue',
        ],
        'docx' => [
            'icono' => 'fas fa-file-word',
            'color' => 'blue',
        ],
        'xls' => [
            'icono' => 'fas fa-file-excel',
            'color' => 'green',
        ],
        'xlsx' => [
            'icono' => 'fas fa-file-excel',
            'color' => 'green',
        ],
        'xlm' => [
            'icono' => 'fas fa-file-excel',
            'color' => 'green',
        ],
        'xlsm' => [
            'icono' => 'fas fa-file-excel',
            'color' => 'green',
        ],
        'ppt' => [
            'icono' => 'fas fa-file-powerpoint',
            'color' => 'orange',
        ],
        'pptx' => [
            'icono' => 'fas fa-file-powerpoint',
            'color' => 'orange',
        ],
        'pptm' => [
            'icono' => 'fas fa-file-powerpoint',
            'color' => 'orange',
        ],
        'png' => [
            'icono' => 'fas fa-file-image',
            'color' => 'darkturquoise',
        ],
        'jpg' => [
            'icono' => 'fas fa-file-image',
            'color' => 'darkturquoise',
        ],
        'jpeg' => [
            'icono' => 'fas fa-file-image',
            'color' => 'darkturquoise',
        ],
    ];

    public const DEFAULT_ARCHIVO = [
        'icono' => 'fas fa-file-alt',
        'color' => '#6c757d',
    ];

    /**
     * Resuelve los datos de visualización (icono y color) a partir de la ruta del archivo.
     */
    public static function getInfoArchivo(?string $enlace): array
    {
        if (empty($enlace)) {
            return self::DEFAULT_ARCHIVO;
        }

        $extension = strtolower(pathinfo($enlace, PATHINFO_EXTENSION));

        return self::MAPA_ARCHIVOS[$extension] ?? self::DEFAULT_ARCHIVO;
    }

    /**
     * Accessor dinámico para el icono del documento.
     * Infiere la clase FontAwesome según la extensión de 'enlace' (o fallback histórico).
     */
    public function getDocumentoAttribute($value): string
    {
        return self::getInfoArchivo($this->enlace)['icono'] ?? ($value ?: self::DEFAULT_ARCHIVO['icono']);
    }

    /**
     * Accessor dinámico para el color del icono.
     * Infiere el color según la extensión de 'enlace' (o fallback histórico).
     */
    public function getColorAttribute($value): string
    {
        return self::getInfoArchivo($this->enlace)['color'] ?? ($value ?: self::DEFAULT_ARCHIVO['color']);
    }

    /**
     * Relación estándar con el usuario creador.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'idUser');
    }

    /**
     * Alias por compatibilidad con código legado.
     */
    public function getUser()
    {
        return $this->belongsTo(User::class, 'idUser');
    }
}
