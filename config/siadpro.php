<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Peso máximo de los documentos adjuntos
    |--------------------------------------------------------------------------
    | Tope en kilobytes para los archivos que suben los usuarios. Es la única
    | fuente de verdad: los controladores lo aplican con `ValidaDocumento` y
    | el frontend lo recibe como prop compartida de Inertia para rechazar el
    | archivo antes de enviarlo.
    */

    'upload_max_kb' => (int) env('SIADPRO_UPLOAD_MAX_KB', 5120),

    /*
    |--------------------------------------------------------------------------
    | Compresores de PDF sugeridos al usuario
    |--------------------------------------------------------------------------
    | Cuando el archivo supera el límite, los modals ofrecen al usuario estas
    | herramientas, que apuntan directamente al apartado de compresión de PDF.
    */

    'pdf_compressors' => [
        [
            'name' => 'iLovePDF',
            'url'  => 'https://www.ilovepdf.com/es/comprimir_pdf',
        ],
        [
            'name' => 'PDF24',
            'url'  => 'https://tools.pdf24.org/es/comprimir-pdf',
        ],
    ],

];
