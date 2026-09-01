@forelse ($informes as $informe)
    <tr data-table-row class="hover:bg-blue-50 transition border-b border-gray-100">
        <td class="px-4 py-3 font-semibold text-blue-600">{{ $informe->nombreInforme }}</td>
        <td class="px-4 py-3">{{ $informe->descripcion ?? '-' }}</td>
        <td class="px-4 py-3 whitespace-nowrap">{{ date('d-m-Y', strtotime($informe->fecha)) }}</td>
        <td class="px-4 py-3 text-center no-export">
            <a href="{{ route('informes.download', $informe->id) }}" target="_blank" title="Descargar documento">
                <i class="{{ $informe->documento }}" style="font-size: 20px; color: {{ $informe->color }}"></i>
            </a>
        </td>
        <td class="px-4 py-3">{{ $informe->name ?? '-' }}</td>
        <td class="px-4 py-3">{{ $informe->cargo ?? '-' }}</td>
        <td class="px-4 py-3">{{ $informe->institucion ?? '-' }}</td>
        <td class="px-4 py-3">{{ $informe->nivelinstitucion ?? '-' }}</td>
        <td class="px-4 py-3">{{ $informe->provincia ?? '-' }}</td>
        <td class="px-4 py-3">{{ $informe->distrito ?? '-' }}</td>
        <td class="px-4 py-3 whitespace-nowrap">{{ $informe->ugel ?? '-' }}</td>
    </tr>
@empty
    {{-- Fila vacía manejada por el motor o en caso de 0 registros del servidor --}}
@endforelse