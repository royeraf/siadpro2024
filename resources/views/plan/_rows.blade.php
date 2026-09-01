@forelse ($plans as $plan)
    <tr data-table-row class="hover:bg-blue-50 transition border-b border-gray-100">
        <td class="px-4 py-3 font-semibold text-blue-600">{{ $plan->nombrePlan }}</td>
        <td class="px-4 py-3">{{ $plan->descripcion ?? '-' }}</td>
        <td class="px-4 py-3 whitespace-nowrap">{{ date('d-m-Y', strtotime($plan->fecha)) }}</td>
        <td class="px-4 py-3 text-center no-export">
            <a href="{{ route('plans.download', $plan->id) }}" target="_blank" title="Descargar documento">
                <i class="{{ $plan->documento }}" style="font-size: 20px; color: {{ $plan->color }}"></i>
            </a>
        </td>
        <td class="px-4 py-3">{{ $plan->getUser->name ?? '-' }}</td>
        <td class="px-4 py-3">{{ $plan->getUser->institucion ?? '-' }}</td>
        <td class="px-4 py-3">{{ $plan->getUser->provincia ?? '-' }}</td>
        <td class="px-4 py-3">{{ $plan->getUser->distrito ?? '-' }}</td>
        <td class="px-4 py-3 whitespace-nowrap">{{ $plan->getUser->ugel ?? '-' }}</td>
        <td class="px-4 py-3 text-center no-export whitespace-nowrap">
            <div class="inline-flex items-center justify-center gap-1">
                <a href="{{ url('/plans/' . $plan->id . '/edit') }}"
                   class="inline-flex items-center justify-center p-1.5 bg-amber-500 hover:bg-amber-600 text-white rounded shadow-sm transition text-xs"
                   title="Editar">
                    <i data-lucide="pencil" class="w-4 h-4"></i>
                </a>
                <form action="{{ route('plans.destroy', $plan->id) }}" method="POST" onsubmit="return confirm('¿Está seguro de eliminar este espacio de lectura en el hogar?');" class="inline-flex items-center justify-center gap-1 m-0">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="inline-flex items-center justify-center p-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded shadow-sm transition text-xs border-0 cursor-pointer"
                            title="Eliminar">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>
                </form>
            </div>
        </td>
    </tr>
@empty
    {{-- Fila vacía manejada por el motor o en caso de 0 registros del servidor --}}
@endforelse