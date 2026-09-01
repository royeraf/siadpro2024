@props([
    'icon',
    'title',
])

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
    <x-section-heading :icon="$icon">{{ $title }}</x-section-heading>

    @if(trim($slot) !== '')
        <div class="d-flex align-items-center gap-2 max-sm:w-full max-sm:mt-2">{{ $slot }}</div>
    @endif
</div>
