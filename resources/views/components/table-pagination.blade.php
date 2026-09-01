@props([
    'id',
    'paginator',
])

<div id="{{ $id }}-pagination" class="mt-3 mb-6 flex justify-center sm:justify-end">
    @if ($paginator->hasPages())
        {{ $paginator->appends(request()->except('page'))->links('vendor.pagination.table-tailwind') }}
    @endif
</div>
