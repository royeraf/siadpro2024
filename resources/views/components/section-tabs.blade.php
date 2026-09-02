@props(['tabs' => []])

@if(count($tabs) > 1)
<div class="flex flex-wrap gap-2 mb-4 -mt-2 p-1.5 bg-gray-100 rounded-lg shadow-inner w-full sm:w-auto">
    @foreach ($tabs as $tab)
        <a href="{{ $tab['url'] }}"
           class="px-4 py-2 text-sm font-semibold rounded-md transition-all duration-200 text-decoration-none {{ $tab['active'] ? 'bg-blue-600 text-white shadow-md' : 'bg-white text-gray-600 hover:bg-blue-50 hover:text-blue-700 border border-gray-200' }}">
            {{ $tab['label'] }}
        </a>
    @endforeach
</div>
@endif
