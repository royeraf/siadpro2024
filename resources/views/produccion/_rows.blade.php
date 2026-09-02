@forelse ($produccions as $produccion)
    <tr data-table-row class="hover:bg-blue-50 transition border-b border-gray-100">
        <td class="px-4 py-3 font-semibold text-blue-600">{{ $produccion->nombreProduccion }}</td>
        <td class="px-4 py-3">{{ $produccion->descripcion ?? '-' }}</td>
        <td class="px-4 py-3 whitespace-nowrap">{{ date('d-m-Y', strtotime($produccion->fecha)) }}</td>
        <td class="px-4 py-3 text-center no-export">
            <a href="#" data-file-viewer data-src="{{ route('visor.stream', ['path' => $produccion->enlace]) }}" data-name="{{ basename($produccion->enlace) }}" data-download="{{ route('produccions.download', $produccion->id) }}" title="Ver documento">
                <i class="{{ $produccion->documento }}" style="font-size: 20px; color: {{ $produccion->color }}"></i>
            </a>
        </td>
        <td class="px-4 py-3">{{ $produccion->getUser->name ?? '-' }}</td>
        <td class="px-4 py-3">{{ $produccion->getUser->institucion ?? '-' }}</td>
        <td class="px-4 py-3">{{ $produccion->getUser->provincia ?? '-' }}</td>
        <td class="px-4 py-3">{{ $produccion->getUser->distrito ?? '-' }}</td>
        <td class="px-4 py-3 whitespace-nowrap">{{ $produccion->getUser->ugel ?? '-' }}</td>
        <td class="px-4 py-3 text-center no-export whitespace-nowrap">
            <div class="inline-flex items-center justify-center gap-1">
                <a href="{{ url('/produccions/' . $produccion->id . '/edit') }}"
                   class="inline-flex items-center justify-center p-1.5 bg-amber-500 hover:bg-amber-600 text-white rounded shadow-sm transition text-xs"
                   title="Editar">
                    <i data-lucide="pencil" class="w-4 h-4"></i>
                </a>
                <form action="{{ route('produccions.destroy', $produccion->id) }}" method="POST" onsubmit="return confirm('¿Está seguro de eliminar esta producción de textos infantiles?');" class="inline-flex items-center justify-center gap-1 m-0">
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