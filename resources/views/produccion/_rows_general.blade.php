@forelse ($produccions as $produccion)
    <tr data-table-row class="hover:bg-blue-50 transition border-b border-gray-100">
        <td class="px-4 py-3 font-semibold text-blue-600">{{ $produccion->nombreProduccion }}</td>
        <td class="px-4 py-3">{{ $produccion->descripcion ?? '-' }}</td>
        <td class="px-4 py-3 whitespace-nowrap">{{ date('d-m-Y', strtotime($produccion->fecha)) }}</td>
        <td class="px-4 py-3 text-center no-export">
            <a href="{{ route('produccions.download', $produccion->id) }}" target="_blank" title="Descargar documento">
                <i class="{{ $produccion->documento }}" style="font-size: 20px; color: {{ $produccion->color }}"></i>
            </a>
        </td>
        <td class="px-4 py-3">{{ $produccion->name ?? '-' }}</td>
        <td class="px-4 py-3">{{ $produccion->cargo ?? '-' }}</td>
        <td class="px-4 py-3">{{ $produccion->institucion ?? '-' }}</td>
        <td class="px-4 py-3">{{ $produccion->nivelinstitucion ?? '-' }}</td>
        <td class="px-4 py-3">{{ $produccion->provincia ?? '-' }}</td>
        <td class="px-4 py-3">{{ $produccion->distrito ?? '-' }}</td>
        <td class="px-4 py-3 whitespace-nowrap">{{ $produccion->ugel ?? '-' }}</td>
    </tr>
@empty
    {{-- Fila vacía manejada por el motor o en caso de 0 registros del servidor --}}
@endforelse