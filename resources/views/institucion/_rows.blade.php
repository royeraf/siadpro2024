@forelse ($institucions as $institucion)
    <tr data-table-row class="hover:bg-blue-50 transition border-b border-gray-100">
        <td class="px-4 py-3 text-center font-bold text-gray-900">{{ $institucion->id }}</td>
        <td class="px-4 py-3 font-semibold text-blue-600">{{ $institucion->nomInstitucion }}</td>
        <td class="px-4 py-3 text-center">
            <span class="inline-block px-2 py-0.5 text-xs font-semibold bg-blue-100 text-blue-800 rounded">
                {{ $institucion->codModular }}
            </span>
        </td>
        <td class="px-4 py-3">{{ $institucion->nivel ?? '-' }}</td>
        <td class="px-4 py-3">{{ $institucion->provincia ?? '-' }}</td>
        <td class="px-4 py-3">{{ $institucion->distrito ?? '-' }}</td>
        <td class="px-4 py-3">{{ $institucion->centropoblado ?? '-' }}</td>
        <td class="px-4 py-3 whitespace-nowrap">{{ $institucion->ugel ?? '-' }}</td>
        <td class="px-4 py-3 text-center no-export whitespace-nowrap">
            <form action="{{ route('institucions.destroy', $institucion->id) }}" method="POST" onsubmit="return confirm('¿Está seguro de eliminar esta institución?');" class="inline-flex items-center justify-center gap-1 m-0">
                <a href="{{ url('/institucions/' . $institucion->id . '/edit') }}"
                   class="inline-flex items-center justify-center p-1.5 bg-amber-500 hover:bg-amber-600 text-white rounded shadow-sm transition text-xs"
                   title="Editar">
                    <i data-lucide="pencil" class="w-4 h-4"></i>
                </a>
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="inline-flex items-center justify-center p-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded shadow-sm transition text-xs border-0 cursor-pointer"
                        title="Eliminar">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                </button>
            </form>
        </td>
    </tr>
@empty
    {{-- Fila vacía manejada por el motor o en caso de 0 registros del servidor --}}
@endforelse
