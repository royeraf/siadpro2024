@forelse ($sectores as $sector)
    <tr data-table-row class="hover:bg-blue-50 transition border-b border-gray-100">
        <td class="px-4 py-3 font-semibold text-blue-600">{{ $sector->nombreSector }}</td>
        <td class="px-4 py-3">{{ $sector->descripcion }}</td>
        <td class="px-4 py-3 whitespace-nowrap">{{ date('d-m-Y', strtotime($sector->fecha)) }}</td>
        <td class="px-4 py-3 text-center no-export">
            <a href="#" data-file-viewer data-src="{{ route('visor.stream', ['path' => $sector->enlace]) }}" data-name="{{ basename($sector->enlace) }}" data-download="{{ route('sectores.download', $sector->id) }}" title="Ver documento">
                <i class="{{ $sector->documento }}" style="font-size: 20px; color: {{ $sector->color }}"></i>
            </a>
        </td>
        <td class="px-4 py-3">{{ $sector->getUser->name ?? '-' }}</td>
        <td class="px-4 py-3">{{ $sector->getUser->institucion ?? '-' }}</td>
        <td class="px-4 py-3">{{ $sector->getUser->provincia ?? '-' }}</td>
        <td class="px-4 py-3">{{ $sector->getUser->distrito ?? '-' }}</td>
        <td class="px-4 py-3 whitespace-nowrap">{{ $sector->getUser->ugel ?? '-' }}</td>
        <td class="px-4 py-3 text-center no-export whitespace-nowrap">
            <div class="inline-flex items-center justify-center gap-1">
                @can('sectores.edit')
                <a href="{{ url('/sectores/' . $sector->id . '/edit') }}"
                   class="inline-flex items-center justify-center p-1.5 bg-amber-500 hover:bg-amber-600 text-white rounded shadow-sm transition text-xs"
                   title="Editar">
                    <i data-lucide="pencil" class="w-4 h-4"></i>
                </a>
                @endcan
                @can('sectores.destroy')
                <form action="{{ route('sectores.destroy', $sector->id) }}" method="POST" onsubmit="return confirm('¿Está seguro de eliminar este sector?');" class="inline-flex items-center justify-center gap-1 m-0">
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
