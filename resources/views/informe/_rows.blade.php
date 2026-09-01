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
        <td class="px-4 py-3">{{ $informe->getUser->name ?? '-' }}</td>
        <td class="px-4 py-3">{{ $informe->getUser->institucion ?? '-' }}</td>
        <td class="px-4 py-3">{{ $informe->getUser->provincia ?? '-' }}</td>
        <td class="px-4 py-3">{{ $informe->getUser->distrito ?? '-' }}</td>
        <td class="px-4 py-3 whitespace-nowrap">{{ $informe->getUser->ugel ?? '-' }}</td>
        <td class="px-4 py-3 text-center no-export whitespace-nowrap">
            <div class="inline-flex items-center justify-center gap-1">
                <a href="{{ url('/informes/' . $informe->id . '/edit') }}"
                   class="inline-flex items-center justify-center p-1.5 bg-amber-500 hover:bg-amber-600 text-white rounded shadow-sm transition text-xs"
                   title="Editar">
                    <i data-lucide="pencil" class="w-4 h-4"></i>
                </a>
                <form action="{{ route('informes.destroy', $informe->id) }}" method="POST" onsubmit="return confirm('¿Está seguro de eliminar esta biblioteca de aula?');" class="inline-flex items-center justify-center gap-1 m-0">
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