@forelse ($sectores as $sector)
    <tr data-table-row class="hover:bg-blue-50 transition border-b border-gray-100">
        <td class="px-4 py-3 font-semibold text-blue-600">{{ $sector->nombreSector }}</td>
        <td class="px-4 py-3">{{ $sector->descripcion ?? '-' }}</td>
        <td class="px-4 py-3 whitespace-nowrap">{{ date('d-m-Y', strtotime($sector->fecha)) }}</td>
        <td class="px-4 py-3 text-center no-export">
            <a href="#" data-file-viewer data-src="{{ route('visor.stream', ['path' => $sector->enlace]) }}" data-name="{{ basename($sector->enlace) }}" data-download="{{ route('sectores.download', $sector->id) }}" title="Ver documento">
                <i class="{{ $sector->documento }}" style="font-size: 20px; color: {{ $sector->color }}"></i>
            </a>
        </td>
        <td class="px-4 py-3">{{ $sector->name ?? '-' }}</td>
        <td class="px-4 py-3">{{ $sector->cargo ?? '-' }}</td>
        <td class="px-4 py-3">{{ $sector->institucion ?? '-' }}</td>
        <td class="px-4 py-3">{{ $sector->nivelinstitucion ?? '-' }}</td>
        <td class="px-4 py-3">{{ $sector->provincia ?? '-' }}</td>
        <td class="px-4 py-3">{{ $sector->distrito ?? '-' }}</td>
        <td class="px-4 py-3 whitespace-nowrap">{{ $sector->ugel ?? '-' }}</td>
    </tr>
@empty
    {{-- Fila vacía manejada por el motor o en caso de 0 registros del servidor --}}
@endforelse
