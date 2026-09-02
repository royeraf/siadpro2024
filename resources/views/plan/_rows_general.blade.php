@forelse ($plans as $plan)
    <tr data-table-row class="hover:bg-blue-50 transition border-b border-gray-100">
        <td class="px-4 py-3 font-semibold text-blue-600">{{ $plan->nombrePlan }}</td>
        <td class="px-4 py-3">{{ $plan->descripcion ?? '-' }}</td>
        <td class="px-4 py-3 whitespace-nowrap">{{ date('d-m-Y', strtotime($plan->fecha)) }}</td>
        <td class="px-4 py-3 text-center no-export">
            <a href="#" data-file-viewer data-src="{{ route('visor.stream', ['path' => $plan->enlace]) }}" data-name="{{ basename($plan->enlace) }}" data-download="{{ route('plans.download', $plan->id) }}" title="Ver documento">
                <i class="{{ $plan->documento }}" style="font-size: 20px; color: {{ $plan->color }}"></i>
            </a>
        </td>
        <td class="px-4 py-3">{{ $plan->name ?? '-' }}</td>
        <td class="px-4 py-3">{{ $plan->cargo ?? '-' }}</td>
        <td class="px-4 py-3">{{ $plan->institucion ?? '-' }}</td>
        <td class="px-4 py-3">{{ $plan->nivelinstitucion ?? '-' }}</td>
        <td class="px-4 py-3">{{ $plan->provincia ?? '-' }}</td>
        <td class="px-4 py-3">{{ $plan->distrito ?? '-' }}</td>
        <td class="px-4 py-3 whitespace-nowrap">{{ $plan->ugel ?? '-' }}</td>
    </tr>
@empty
    {{-- Fila vacía manejada por el motor o en caso de 0 registros del servidor --}}
@endforelse