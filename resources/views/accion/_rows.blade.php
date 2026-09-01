@forelse ($accions as $accion)
    <tr data-table-row class="hover:bg-blue-50 transition border-b border-gray-100">
        <td class="px-4 py-3 font-semibold text-blue-600">{{ $accion->nombreAccion }}</td>
        <td class="px-4 py-3">{{ $accion->lugar }}</td>
        <td class="px-4 py-3 whitespace-nowrap">{{ date('d-m-Y', strtotime($accion->fecha)) }}</td>
        <td class="px-4 py-3 text-center no-export">
            <a href="{{ route('accions.download', $accion->id) }}" target="_blank" title="Descargar documento">
                <i class="{{ $accion->documento }}" style="font-size: 20px; color: {{ $accion->color }}"></i>
            </a>
        </td>
        <td class="px-4 py-3">{{ $accion->getUser->name ?? '-' }}</td>
        <td class="px-4 py-3">{{ $accion->getUser->institucion ?? '-' }}</td>
        <td class="px-4 py-3">{{ $accion->getUser->provincia ?? '-' }}</td>
        <td class="px-4 py-3">{{ $accion->getUser->distrito ?? '-' }}</td>
        <td class="px-4 py-3 whitespace-nowrap">{{ $accion->getUser->ugel ?? '-' }}</td>
        <td class="px-4 py-3 text-center no-export whitespace-nowrap">
            <div class="inline-flex items-center justify-center gap-1">
                <a href="{{ url('/accions/' . $accion->id . '/edit') }}"
                   class="inline-flex items-center justify-center p-1.5 bg-amber-500 hover:bg-amber-600 text-white rounded shadow-sm transition text-xs"
                   title="Editar">
                    <i data-lucide="pencil" class="w-4 h-4"></i>
                </a>
                <form action="{{ route('accions.destroy', $accion->id) }}" method="POST" onsubmit="return confirm('¿Está seguro de eliminar esta acción?');" class="inline-flex items-center justify-center gap-1 m-0">
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
