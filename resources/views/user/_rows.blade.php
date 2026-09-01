@forelse ($users as $user)
    <tr data-table-row class="hover:bg-blue-50 transition border-b border-gray-100">
        <td class="px-4 py-3 text-center">
            @if($user->estado == 1)
                <span class="inline-block px-2 py-0.5 text-xs font-semibold bg-emerald-100 text-emerald-800 rounded">Activo</span>
            @else
                <span class="inline-block px-2 py-0.5 text-xs font-semibold bg-rose-100 text-rose-800 rounded">Inactivo</span>
            @endif
        </td>
        <td class="px-4 py-3 text-center font-bold text-gray-900">{{ $user->id }}</td>
        <td class="px-4 py-3">
            <span class="inline-block px-2 py-0.5 text-xs font-semibold bg-blue-100 text-blue-800 rounded">
                {{ $user->dni }}
            </span>
        </td>
        <td class="px-4 py-3 font-semibold text-blue-600">{{ $user->name }}</td>
        <td class="px-4 py-3">{{ $user->email }}</td>
        <td class="px-4 py-3">{{ $user->cargo ?? '-' }}</td>
        <td class="px-4 py-3">{{ $user->institucion ?? '-' }}</td>
        <td class="px-4 py-3 whitespace-nowrap">{{ $user->ugel ?? '-' }}</td>
        <td class="px-4 py-3">{{ $user->nivelinstitucion ?? '-' }}</td>
        <td class="px-4 py-3">{{ $user->provincia ?? '-' }}</td>
        <td class="px-4 py-3">{{ $user->distrito ?? '-' }}</td>
        <td class="px-4 py-3 text-center no-export whitespace-nowrap">
            <div class="inline-flex items-center justify-center gap-1">
                <a href="{{ url('/users/' . $user->id . '/edit') }}"
                   class="inline-flex items-center justify-center p-1.5 bg-blue-500 hover:bg-blue-600 text-white rounded shadow-sm transition text-xs"
                   title="Asignar rol">
                    <i data-lucide="shield" class="w-4 h-4"></i>
                </a>
                <a href="{{ url('/usuarios/' . $user->id . '/edit') }}"
                   class="inline-flex items-center justify-center p-1.5 bg-amber-500 hover:bg-amber-600 text-white rounded shadow-sm transition text-xs"
                   title="Editar datos">
                    <i data-lucide="pencil" class="w-4 h-4"></i>
                </a>
                @if($user->estado == 1)
                    <a href="{{ route('cambiarEstado', $user->id) }}"
                       onclick="return confirm('¿Está seguro de desactivar a {{ addslashes($user->name) }}?');"
                       class="inline-flex items-center justify-center p-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded shadow-sm transition text-xs"
                       title="Desactivar">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </a>
                @else
                    <a href="{{ route('cambiarEstado', $user->id) }}"
                       class="inline-flex items-center justify-center p-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded shadow-sm transition text-xs"
                       title="Activar">
                        <i data-lucide="check" class="w-4 h-4"></i>
                    </a>
                @endif
            </div>
        </td>
    </tr>
@empty
    {{-- Fila vacía manejada por el motor o en caso de 0 registros del servidor --}}
@endforelse
