<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

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
     * Documentos de oficina que se convierten a PDF para su previsualización.
     */
    private const OFFICE_EXTS = [
        'doc', 'docx', 'rtf', 'odt',
        'ppt', 'pptx', 'odp',
        'xls', 'xlsx', 'ods',
    ];

    /**
     * Sirve un archivo almacenado en storage/app/public de forma inline
     * para el visualizador del navegador.
     */
    public function stream(Request $request): Response
    {
        $absolute = $this->resolveStoredPath((string) $request->query('path', ''));
        $mime = $this->mimeFor(strtolower(pathinfo($absolute, PATHINFO_EXTENSION)));

        return $this->inlineFile($absolute, $mime);
    }

    /**
     * Convierte un documento de oficina a PDF (con caché) y lo sirve inline.
     * Si el archivo ya es PDF, lo entrega directamente.
     */
    public function pdf(Request $request): Response
    {
        $absolute = $this->resolveStoredPath((string) $request->query('path', ''));
        $ext = strtolower(pathinfo($absolute, PATHINFO_EXTENSION));

        if ($ext === 'pdf') {
            return $this->inlineFile($absolute, 'application/pdf');
        }

        if (! in_array($ext, self::OFFICE_EXTS, true)) {
            abort(415, 'El archivo no se puede convertir a PDF.');
        }

        @set_time_limit(150);
        $pdf = $this->convertToPdf($absolute);

        if ($pdf === null) {
            abort(502, 'No se pudo generar la vista previa del documento.');
        }

        return $this->inlineFile(
            $pdf,
            'application/pdf',
            pathinfo($absolute, PATHINFO_FILENAME) . '.pdf'
        );
    }

    /**
     * Valida el path solicitado y devuelve la ruta absoluta del archivo.
     */
    private function resolveStoredPath(string $path): string
    {
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

        return storage_path('app/' . $fullPath);
    }

    /**
     * Convierte (o recupera de caché) un documento de oficina a PDF.
     */
    private function convertToPdf(string $absolute): ?string
    {
        if (! is_file($absolute)) {
            return null;
        }

        $cacheDir = storage_path('app/previews');
        if (! is_dir($cacheDir) && ! @mkdir($cacheDir, 0755, true) && ! is_dir($cacheDir)) {
            return null;
        }

        $key = sha1($absolute . '|' . filesize($absolute) . '|' . filemtime($absolute));
        $cached = $cacheDir . DIRECTORY_SEPARATOR . $key . '.pdf';

        if (is_file($cached)) {
            return $cached;
        }

        $binary = $this->libreOfficeBinary();
        if ($binary === null) {
            return null;
        }

        $tmp = $cacheDir . DIRECTORY_SEPARATOR . 'tmp_' . $key . '_' . getmypid();
        $profile = $tmp . DIRECTORY_SEPARATOR . 'profile';
        if (! is_dir($profile) && ! @mkdir($profile, 0755, true) && ! is_dir($profile)) {
            return null;
        }

        try {
            $process = new Process([
                $binary,
                '--headless',
                '--norestore',
                '--invisible',
                '--nodefault',
                '--nolockcheck',
                '-env:UserInstallation=file://' . str_replace('\\', '/', $profile),
                '--convert-to', 'pdf',
                '--outdir', $tmp,
                $absolute,
            ], null, ['HOME' => $tmp]);

            $process->setTimeout(120);
            $process->run();

            $out = $tmp . DIRECTORY_SEPARATOR . pathinfo($absolute, PATHINFO_FILENAME) . '.pdf';

            if (! is_file($out)) {
                $found = glob($tmp . DIRECTORY_SEPARATOR . '*.pdf');
                $out = $found[0] ?? null;
            }

            if (! $out || ! is_file($out)) {
                return null;
            }

            if (! @rename($out, $cached)) {
                @copy($out, $cached);
            }

            return is_file($cached) ? $cached : null;
        } catch (\Throwable $e) {
            return null;
        } finally {
            $this->deleteDirectory($tmp);
        }
    }

    /**
     * Localiza el ejecutable de LibreOffice/soffice.
     */
    private function libreOfficeBinary(): ?string
    {
        $configured = env('LIBREOFFICE_BINARY', 'soffice');
        $finder = new ExecutableFinder();
        $found = $finder->find($configured, null, [
            '/usr/bin', '/usr/local/bin', '/opt/libreoffice/program', '/snap/bin',
        ]);

        return $found ?: null;
    }

    private function deleteDirectory(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }

        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($items as $item) {
            $item->isDir() ? @rmdir($item->getPathname()) : @unlink($item->getPathname());
        }

        @rmdir($dir);
    }

    private function inlineFile(string $absolute, string $mime, ?string $downloadName = null): Response
    {
        return response()->stream(function () use ($absolute) {
            readfile($absolute);
        }, 200, [
            'Content-Type' => $mime,
            'Content-Length' => (string) filesize($absolute),
            'Content-Disposition' => 'inline; filename="' . addslashes($downloadName ?? basename($absolute)) . '"',
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
