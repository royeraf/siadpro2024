@forelse ($evidencias as $evidencia)
    <tr data-table-row class="hover:bg-blue-50 transition border-b border-gray-100">
        <td class="px-4 py-3 font-semibold text-blue-600">{{ $evidencia->nombreEvidencia }}</td>
        <td class="px-4 py-3">{{ $evidencia->descripcion }}</td>
        <td class="px-4 py-3 whitespace-nowrap">{{ date('d-m-Y', strtotime($evidencia->fecha)) }}</td>
        <td class="px-4 py-3 text-center no-export">
            <a href="#" data-file-viewer data-src="{{ route('visor.stream', ['path' => $evidencia->enlace]) }}" data-name="{{ basename($evidencia->enlace) }}" data-download="{{ route('evidencias.download', $evidencia->id) }}" title="Ver documento">
                <i class="{{ $evidencia->documento }}" style="font-size: 20px; color: {{ $evidencia->color }}"></i>
            </a>
        </td>
        <td class="px-4 py-3">{{ $evidencia->getUser->name ?? '-' }}</td>
        <td class="px-4 py-3">{{ $evidencia->getUser->institucion ?? '-' }}</td>
        <td class="px-4 py-3">{{ $evidencia->getUser->provincia ?? '-' }}</td>
        <td class="px-4 py-3">{{ $evidencia->getUser->distrito ?? '-' }}</td>
        <td class="px-4 py-3 whitespace-nowrap">{{ $evidencia->getUser->ugel ?? '-' }}</td>
        <td class="px-4 py-3 text-center no-export whitespace-nowrap">
            <div class="inline-flex items-center justify-center gap-1">
                @can('evidencias.edit')
                <a href="{{ url('/evidencias/' . $evidencia->id . '/edit') }}"
                   class="inline-flex items-center justify-center p-1.5 bg-amber-500 hover:bg-amber-600 text-white rounded shadow-sm transition text-xs"
                   title="Editar">
                    <i data-lucide="pencil" class="w-4 h-4"></i>
                </a>
                @endcan
                @can('evidencias.destroy')
                <form action="{{ route('evidencias.destroy', $evidencia->id) }}" method="POST" onsubmit="return confirm('¿Está seguro de eliminar esta asistencia técnica?');" class="inline-flex items-center justify-center gap-1 m-0">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="inline-flex items-center justify-center p-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded shadow-sm transition text-xs border-0 cursor-pointer"
                            title="Eliminar">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>
                </form>
                @endcan
            </div>
        </td>
    </tr>
@empty
    {{-- Fila vacía manejada por el motor o en caso de 0 registros del servidor --}}
@endforelse
