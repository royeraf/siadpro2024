<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ReniecService
{
    protected ?string $apiUrl;
    protected ?string $token;
    protected ?string $contingencyUrl1;
    protected ?string $contingencyToken1;
    protected ?string $contingencyUrl2;
    protected ?string $contingencyToken2;

    public function __construct()
    {
        $this->apiUrl = config('services.reniec.url');
        $this->token = config('services.reniec.token');
        $this->contingencyUrl1 = config('services.perudevs.url');
        $this->contingencyToken1 = config('services.perudevs.token');
        $this->contingencyUrl2 = config('services.apiperu.url');
        $this->contingencyToken2 = config('services.apiperu.token');
    }

    /**
     * Consultar datos de una persona por DNI.
     *
     * @param string $dni
     * @return array
     */
    public function consultarDni(string $dni): array
    {
        $dni = trim($dni);

        // Validar que el DNI tenga 8 dígitos
        if (!preg_match('/^\d{8}$/', $dni)) {
            return [
                'success' => false,
                'message' => 'El DNI debe tener exactamente 8 dígitos numéricos.',
                'data' => null,
            ];
        }

        // --- 1. PROVEEDOR PRINCIPAL (Decolecta) ---
        if ($this->apiUrl && $this->token) {
            try {
                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $this->token,
                ])
                ->timeout(8)
                ->get($this->apiUrl, [
                    'numero' => $dni,
                ]);

                if ($response->successful()) {
                    $data = $response->json();

                    if (!isset($data['error']) || !$data['error']) {
                        if (!empty($data['first_name']) || !empty($data['full_name'])) {
                            $nombres = mb_strtoupper(trim($data['first_name'] ?? ''), 'UTF-8');
                            $paterno = mb_strtoupper(trim($data['first_last_name'] ?? ''), 'UTF-8');
                            $materno = mb_strtoupper(trim($data['second_last_name'] ?? ''), 'UTF-8');
                            $apellidos = trim("{$paterno} {$materno}");
                            $nombreCompleto = trim("{$apellidos} {$nombres}");

                            return [
                                'success' => true,
                                'source' => 'decolecta',
                                'message' => 'Consulta exitosa en RENIEC',
                                'data' => [
                                    'dni' => $data['document_number'] ?? $dni,
                                    'nombres' => $nombres,
                                    'apellido_paterno' => $paterno,
                                    'apellido_materno' => $materno,
                                    'apellidos' => $apellidos,
                                    'nombre_completo' => $nombreCompleto,
                                ],
                            ];
                        }
                    }
                } else {
                    Log::warning('RENIEC Decolecta API Error', [
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('RENIEC Decolecta Exception', ['error' => $e->getMessage()]);
            }
        }

        // --- 2. CONTINGENCIA 1 (PeruDevs) ---
        $contingencyData1 = $this->consultarContingenciaPerudevs($dni);
        if ($contingencyData1) {
            return [
                'success' => true,
                'source' => 'perudevs',
                'message' => 'Consulta exitosa en RENIEC (Contingencia 1)',
                'data' => $contingencyData1,
            ];
        }

        // --- 3. CONTINGENCIA 2 (ApiPeru) ---
        $contingencyData2 = $this->consultarContingenciaApiPeru($dni);
        if ($contingencyData2) {
            return [
                'success' => true,
                'source' => 'apiperu',
                'message' => 'Consulta exitosa en RENIEC (Contingencia 2)',
                'data' => $contingencyData2,
            ];
        }

        return [
            'success' => false,
            'message' => 'No se encontraron datos en RENIEC para el DNI ingresado.',
            'data' => null,
        ];
    }

    /**
     * Consulta de contingencia 1 usando api.perudevs.com
     */
    protected function consultarContingenciaPerudevs(string $dni): ?array
    {
        if (!$this->contingencyUrl1 || !$this->contingencyToken1) {
            return null;
        }

        try {
            $response = Http::timeout(8)->get($this->contingencyUrl1, [
                'document' => $dni,
                'key' => $this->contingencyToken1,
            ]);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['estado']) && $data['estado'] === true && isset($data['resultado'])) {
                    $personData = $data['resultado'];
                    $nombres = mb_strtoupper(trim($personData['nombres'] ?? ''), 'UTF-8');
                    $paterno = mb_strtoupper(trim($personData['apellido_paterno'] ?? ''), 'UTF-8');
                    $materno = mb_strtoupper(trim($personData['apellido_materno'] ?? ''), 'UTF-8');
                    $apellidos = trim("{$paterno} {$materno}");
                    $nombreCompleto = trim("{$apellidos} {$nombres}");

                    return [
                        'dni' => $personData['id'] ?? $dni,
                        'nombres' => $nombres,
                        'apellido_paterno' => $paterno,
                        'apellido_materno' => $materno,
                        'apellidos' => $apellidos,
                        'nombre_completo' => $nombreCompleto,
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::error('PeruDevs Contingency Exception', ['error' => $e->getMessage()]);
        }

        return null;
    }

    /**
     * Consulta de contingencia 2 usando apiperu.dev
     */
    protected function consultarContingenciaApiPeru(string $dni): ?array
    {
        if (!$this->contingencyUrl2 || !$this->contingencyToken2) {
            return null;
        }

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->contingencyToken2,
            ])
            ->timeout(8)
            ->post($this->contingencyUrl2, [
                'dni' => $dni,
            ]);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['success']) && $data['success'] === true && isset($data['data'])) {
                    $personData = $data['data'];
                    $nombres = mb_strtoupper(trim($personData['nombres'] ?? ''), 'UTF-8');
                    $paterno = mb_strtoupper(trim($personData['apellido_paterno'] ?? ''), 'UTF-8');
                    $materno = mb_strtoupper(trim($personData['apellido_materno'] ?? ''), 'UTF-8');
                    $apellidos = trim("{$paterno} {$materno}");
                    $nombreCompleto = trim("{$apellidos} {$nombres}");

                    return [
                        'dni' => $personData['numero'] ?? $dni,
                        'nombres' => $nombres,
                        'apellido_paterno' => $paterno,
                        'apellido_materno' => $materno,
                        'apellidos' => $apellidos,
                        'nombre_completo' => $nombreCompleto,
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::error('ApiPeru Contingency Exception', ['error' => $e->getMessage()]);
        }

        return null;
    }
}
