<?php

namespace App\Http\Controllers\Concerns;

/**
 * Reglas y mensajes de validación para el campo `documento`.
 *
 * El peso máximo sale de `config('siadpro.upload_max_kb')` (5 MB por defecto)
 * para que el backend y los modals del frontend compartan un único límite.
 *
 * Si el recurso necesita restringir además el tipo de archivo, declara la
 * propiedad `$documentoTypeRule` con la regla completa (`mimes:...` o
 * `mimetypes:...`). Si no se declara, solo se valida que sea un archivo.
 */
trait ValidaDocumento
{
    /** Peso máximo permitido en kilobytes. */
    public static function uploadMaxKb(): int
    {
        return (int) config('siadpro.upload_max_kb', 5120);
    }

    /** Peso máximo permitido expresado en megabytes (para los mensajes). */
    public static function uploadMaxMb(): int
    {
        return (int) round(static::uploadMaxKb() / 1024);
    }

    /**
     * Reglas de validación del campo `documento`.
     *
     * @param  bool  $required  `true` en `store()`, `false` en `update()`.
     */
    protected function documentoRules(bool $required = true): array
    {
        $rules = [$required ? 'required' : 'nullable', 'file'];

        $typeRule = property_exists($this, 'documentoTypeRule') ? $this->documentoTypeRule : '';

        if ($typeRule !== '') {
            $rules[] = $typeRule;
        }

        $rules[] = 'max:' . static::uploadMaxKb();

        return $rules;
    }

    /**
     * Mensajes de validación del campo `documento`.
     */
    protected function documentoMessages(bool $required = true): array
    {
        $messages = [
            'documento.max'       => 'El archivo no debe pesar más de ' . static::uploadMaxMb() . 'MB.',
            'documento.mimes'     => 'El tipo de archivo no es compatible.',
            'documento.mimetypes' => 'El tipo de archivo no es compatible.',
        ];

        if ($required) {
            $messages['documento.required'] = 'Debe adjuntar un archivo para el registro.';
        }

        return $messages;
    }
}
