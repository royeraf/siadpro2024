@forelse ($accions as $accion)
    <tr data-table-row class="hover:bg-blue-50 transition border-b border-gray-100">
        <td class="px-4 py-3 font-semibold text-blue-600">{{ $accion->nombreAccion }}</td>
        <td class="px-4 py-3">{{ $accion->descripcion ?? '-' }}</td>
        <td class="px-4 py-3">{{ $accion->lugar }}</td>
        <td class="px-4 py-3 whitespace-nowrap">{{ date('d-m-Y', strtotime($accion->fecha)) }}</td>
        <td class="px-4 py-3 text-center no-export">
            <a href="#" data-file-viewer data-src="{{ route('visor.stream', ['path' => $accion->enlace]) }}" data-name="{{ basename($accion->enlace) }}" data-download="{{ route('accions.download', $accion->id) }}" title="Ver documento">
                <i class="{{ $accion->documento }}" style="font-size: 20px; color: {{ $accion->color }}"></i>
            </a>
        </td>
        <td class="px-4 py-3">{{ $accion->name ?? '-' }}</td>
        <td class="px-4 py-3">{{ $accion->cargo ?? '-' }}</td>
        <td class="px-4 py-3">{{ $accion->institucion ?? '-' }}</td>
        <td class="px-4 py-3">{{ $accion->nivelinstitucion ?? '-' }}</td>
        <td class="px-4 py-3">{{ $accion->provincia ?? '-' }}</td>
        <td class="px-4 py-3">{{ $accion->distrito ?? '-' }}</td>
        <td class="px-4 py-3 whitespace-nowrap">{{ $accion->ugel ?? '-' }}</td>
    </tr>
@empty
    {{-- Fila vacía manejada por el motor o en caso de 0 registros del servidor --}}
@endforelse
