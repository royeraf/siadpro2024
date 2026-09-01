@forelse ($evidencias as $evidencia)
    <tr data-table-row class="hover:bg-blue-50 transition border-b border-gray-100">
        <td class="px-4 py-3 font-semibold text-blue-600">{{ $evidencia->nombreEvidencia }}</td>
        <td class="px-4 py-3">{{ $evidencia->descripcion ?? '-' }}</td>
        <td class="px-4 py-3 whitespace-nowrap">{{ date('d-m-Y', strtotime($evidencia->fecha)) }}</td>
        <td class="px-4 py-3 text-center no-export">
            <a href="{{ route('evidencias.download', $evidencia->id) }}" target="_blank" title="Descargar documento">
                <i class="{{ $evidencia->documento }}" style="font-size: 20px; color: {{ $evidencia->color }}"></i>
            </a>
        </td>
        <td class="px-4 py-3">{{ $evidencia->name ?? '-' }}</td>
        <td class="px-4 py-3">{{ $evidencia->cargo ?? '-' }}</td>
        <td class="px-4 py-3">{{ $evidencia->institucion ?? '-' }}</td>
        <td class="px-4 py-3">{{ $evidencia->nivelinstitucion ?? '-' }}</td>
        <td class="px-4 py-3">{{ $evidencia->provincia ?? '-' }}</td>
        <td class="px-4 py-3">{{ $evidencia->distrito ?? '-' }}</td>
        <td class="px-4 py-3 whitespace-nowrap">{{ $evidencia->ugel ?? '-' }}</td>
    </tr>
@empty
    {{-- Fila vacía manejada por el motor o en caso de 0 registros del servidor --}}
@endforelse
