@forelse ($agendas as $agenda)
    <tr data-table-row class="hover:bg-blue-50 transition border-b border-gray-100">
        <td class="px-4 py-3 font-semibold text-blue-600">{{ $agenda->nomDocente }}</td>
        <td class="px-4 py-3">{{ $agenda->title ?? '-' }}</td>
        <td class="px-4 py-3">{{ $agenda->evento ?? '-' }}</td>
        <td class="px-4 py-3 whitespace-nowrap">{{ date('d-m-Y', strtotime($agenda->start)) }}</td>
        <td class="px-4 py-3 whitespace-nowrap">{{ date('d-m-Y', strtotime($agenda->end)) }}</td>
        <td class="px-4 py-3">{{ $agenda->institucion ?? '-' }}</td>
        <td class="px-4 py-3">{{ $agenda->nivelinstitucion ?? '-' }}</td>
        <td class="px-4 py-3">{{ $agenda->provincia ?? '-' }}</td>
        <td class="px-4 py-3">{{ $agenda->distrito ?? '-' }}</td>
        <td class="px-4 py-3 whitespace-nowrap">{{ $agenda->ugel ?? '-' }}</td>
    </tr>
@empty
    {{-- Fila vacía manejada por el motor o en caso de 0 registros del servidor --}}
@endforelse