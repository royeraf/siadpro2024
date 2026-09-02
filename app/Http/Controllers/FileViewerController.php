<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class FileViewerController extends Controller
{
    /**
     * Carpetas permitidas dentro de storage/app/public.
     */
    private const ALLOWED_DIRS = [
        'accion', 'difusion', 'evidencia', 'informe',
        'plan', 'planA', 'produccion', 'sector',
    ];

    /**
     * Sirve un archivo almacenado en storage/app/public de forma inline
     * para el visualizador del navegador.
     */
    public function stream(Request $request): Response
    {
        $path = (string) $request->query('path', '');

        // Normalizar y validar el path solicitado
        $path = str_replace('\\', '/', $path);
        $path = ltrim($path, '/');

        $parts = explode('/', $path);

        if (count($parts) !== 2 || ! in_array($parts[0], self::ALLOWED_DIRS, true)) {
            abort(404);
        }

        // Bloquear traversal, nombres vacíos, separadores de ruta y caracteres
        // de control. El nombre del archivo se genera a partir de un campo de
        // texto libre (p. ej. "Nombre de la Acción"), así que puede legítimamente
        // contener comas, apóstrofes, °, &, dos puntos, etc. — no se restringe
        // a una lista de caracteres "seguros" porque eso bloqueaba archivos reales.
        if (
            str_contains($path, '..')
            || $parts[1] === ''
            || str_contains($parts[1], '/')
            || preg_match('/[\x00-\x1F]/', $parts[1])
        ) {
            abort(404);
        }

        $fullPath = 'public/' . $path;

        if (! Storage::exists($fullPath)) {
            abort(404);
        }

        $absolute = storage_path('app/' . $fullPath);
        $mime = $this->mimeFor(strtolower(pathinfo($absolute, PATHINFO_EXTENSION)));

        return response()->stream(function () use ($absolute) {
            readfile($absolute);
        }, 200, [
            'Content-Type' => $mime,
            'Content-Length' => (string) filesize($absolute),
            'Content-Disposition' => 'inline; filename="' . addslashes(basename($absolute)) . '"',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }

    private function mimeFor(string $ext): string
    {
        return match ($ext) {
            'pdf' => 'application/pdf',
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'bmp' => 'image/bmp',
            'svg' => 'image/svg+xml',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'csv' => 'text/csv',
            'txt' => 'text/plain',
            'ppt' => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            default => 'application/octet-stream',
        };
    }
}
