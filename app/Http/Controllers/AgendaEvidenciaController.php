<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ValidaDocumento;
use App\Models\Agenda;
use App\Models\AgendaEvidencia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AgendaEvidenciaController extends Controller
{
    use ValidaDocumento;

    /**
     * Mismos tipos que el resto de módulos con adjuntos ("todos los tipos de
     * archivos soportados actualmente en el sistema").
     */
    protected $documentoTypeRule = 'mimes:pdf,doc,docx,xls,xlsx,xlm,xlsm,ppt,pptx,pptm,png,jpg,jpeg';

    /** Carpeta dentro de storage/app/public (debe estar en FileViewerController). */
    private const CARPETA = 'agenda_evidencia';

    /** Máximo de archivos por subida. */
    private const MAX_ARCHIVOS = 10;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:agendas.evidencia')->only('store', 'destroy');
    }

    /**
     * Devuelve los días programados de la agenda con sus archivos, para el
     * desglose por día (lectura). Lo usan "Mis registros" y las vistas de
     * solo lectura (Director / General / UGEL).
     */
    public function index(Agenda $agenda)
    {
        $this->autorizarVista($agenda);

        $agenda->load([
            'dias' => fn ($q) => $q->orderBy('fecha'),
            'dias.evidencias' => fn ($q) => $q->where('estado', 1)->orderBy('id'),
        ]);

        return response()->json([
            'agenda' => [
                'id'         => $agenda->id,
                'title'      => $agenda->title,
                'evento'     => $agenda->evento,
                'color'      => $agenda->color,
                'seccion'    => $agenda->seccion,
                'nomDocente' => $agenda->nomDocente,
            ],
            'dias' => $agenda->dias->map(fn ($dia) => [
                'id'          => $dia->id,
                'fecha'       => $dia->fecha?->format('Y-m-d'),
                'hora_inicio' => $dia->hora_inicio ? substr($dia->hora_inicio, 0, 5) : null,
                'hora_fin'    => $dia->hora_fin ? substr($dia->hora_fin, 0, 5) : null,
                'evidencias'  => $dia->evidencias->map(fn ($ev) => [
                    'id'              => $ev->id,
                    'enlace'          => $ev->enlace,
                    'nombre_original' => $ev->nombre_original,
                    'documento'       => $ev->documento,
                    'color'           => $ev->color,
                ])->values(),
            ])->values(),
        ]);
    }

    /**
     * Sube uno o varios archivos a un día programado. Solo el dueño de la agenda.
     */
    public function store(Request $request, Agenda $agenda)
    {
        abort_unless((int) $agenda->idUser === Auth::id(), 403);

        $request->validate([
            'agenda_dia_id' => 'required|integer',
            'documento'     => 'required|array|min:1|max:' . self::MAX_ARCHIVOS,
            'documento.*'   => array_merge(
                ['file'],
                [$this->documentoTypeRule],
                ['max:' . static::uploadMaxKb()]
            ),
        ], [
            'agenda_dia_id.required' => 'Debe indicar el día al que pertenece el archivo.',
            'documento.required'     => 'Debe adjuntar al menos un archivo.',
            'documento.array'        => 'Debe adjuntar al menos un archivo.',
            'documento.min'          => 'Debe adjuntar al menos un archivo.',
            'documento.max'          => 'No se pueden subir más de ' . self::MAX_ARCHIVOS . ' archivos a la vez.',
            'documento.*.file'       => 'Uno de los adjuntos no es un archivo válido.',
            'documento.*.mimes'      => 'El tipo de archivo no es compatible.',
            'documento.*.max'        => 'El archivo no debe pesar más de ' . static::uploadMaxMb() . 'MB.',
        ]);

        // El día debe pertenecer a esta agenda (evita asociar a días de otra).
        $dia = $agenda->dias()->whereKey($request->input('agenda_dia_id'))->firstOrFail();

        if (! Storage::exists('public/' . self::CARPETA)) {
            Storage::makeDirectory('public/' . self::CARPETA);
            Storage::disk('public')->setVisibility(self::CARPETA, 'public');
        }

        $subidos = 0;

        foreach ($request->file('documento') as $file) {
            $extension      = strtolower($file->getClientOriginalExtension());
            $nombreOriginal = $file->getClientOriginalName();

            // El nombre se guarda tal cual (menos separadores de ruta, control y
            // '..', que FileViewerController rechaza al previsualizar). El resto
            // de caracteres reales (comas, apóstrofes, °, etc.) sí se admiten.
            $base = preg_replace('/[\\\\\/\x00-\x1F]/', '_', pathinfo($nombreOriginal, PATHINFO_FILENAME));
            $base = preg_replace('/\.{2,}/', '_', $base);
            $base = trim($base, ". \t");
            $base = ($base === '' ? 'archivo' : $base);

            $archivo = $base . '_' . now()->format('Ymd_His_u') . '_' . Str::random(4) . '.' . $extension;

            Storage::putFileAs('public/' . self::CARPETA, $file, $archivo);

            AgendaEvidencia::create([
                'agenda_dia_id'   => $dia->id,
                'enlace'          => self::CARPETA . '/' . $archivo,
                'nombre_original' => $nombreOriginal,
                'idUser'          => Auth::id(),
                'estado'          => 1,
            ]);

            $subidos++;
        }

        $message = "¡{$subidos} archivo(s) subido(s) con éxito!";

        // La subida se hace con axios (Accept: application/json): se responde JSON.
        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Elimina (lógicamente) un archivo. Solo el dueño de la agenda.
     */
    public function destroy(AgendaEvidencia $evidencia)
    {
        $dia    = $evidencia->dia()->with('agenda')->first();
        $agenda = $dia?->agenda;

        abort_unless($agenda && (int) $agenda->idUser === Auth::id(), 403);

        Storage::delete('public/' . $evidencia->enlace);

        $evidencia->estado = 0;
        $evidencia->save();

        return redirect()->back()->with('success', '¡Archivo eliminado con éxito!');
    }

    public function download(AgendaEvidencia $evidencia)
    {
        $agenda = $evidencia->dia()->with('agenda')->first()?->agenda;
        abort_unless($agenda, 404);

        $this->autorizarVista($agenda);

        $path = storage_path('app/public/' . $evidencia->enlace);
        abort_unless(is_file($path), 404);

        return response()->download($path, $evidencia->nombre_original ?: basename($evidencia->enlace));
    }

    /**
     * Control de acceso de lectura: dueño, General (Admin/EspecDRE), o alcance
     * Director (misma institución) / UGEL (misma ugel) — igual que las pestañas
     * de la sección.
     */
    private function autorizarVista(Agenda $agenda): void
    {
        $user = Auth::user();

        if ((int) $agenda->idUser === $user->id) {
            return;
        }

        if ($user->can('agendas.general')) {
            return;
        }

        $agenda->loadMissing('user');
        $duenio = $agenda->user;

        abort_unless($duenio, 403);

        if ($user->can('agendas.view') && $duenio->institucion && $duenio->institucion === $user->institucion) {
            return;
        }

        if ($user->can('agendas.ugel') && $duenio->ugel && $duenio->ugel === $user->ugel) {
            return;
        }

        abort(403);
    }
}
