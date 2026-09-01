@props([
    'name',
    'label',
    'icon' => null,
    'type' => 'text',
    'placeholder' => '',
    'options' => null,
    'value' => null,
    'searchable' => false,
])

@php
    $currentValue = $value ?? request($name);
@endphp

<div>
    <label for="{{ $name }}" class="block text-xs font-semibold text-gray-600 mb-1">
        @if($icon)
            <i data-lucide="{{ $icon }}" class="w-3.5 h-3.5 mr-1 inline-block align-text-bottom text-gray-400"></i>
        @endif
        {{ $label }}
    </label>

    @if($options !== null && $searchable)
        <div x-data="{
                open: false,
                query: @js((string) $currentValue),
                selected: @js((string) $currentValue),
                options: @js(collect($options)->values()),
                get filtered() {
                    if (!this.query) return this.options;
                    const q = this.query.toLowerCase();
                    return this.options.filter(o => o.toLowerCase().includes(q));
                },
                select(opt) {
                    this.selected = opt;
                    this.query = opt;
                    this.open = false;
                },
                clear() {
                    this.selected = '';
                    this.query = '';
                    this.open = false;
                }
             }" class="relative" @click.outside="open = false">
            <input type="hidden" name="{{ $name }}" :value="selected">
            <input type="text" id="{{ $name }}" x-model="query" autocomplete="off"
                   @focus="open = true" @click="open = true" @input="open = true; if (query === '') selected = ''"
                   placeholder="{{ $placeholder ?: '-- Todos --' }}"
                   class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 py-2 pl-2.5 pr-8">
            <button type="button" x-show="query.length > 0" @click="clear()"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                <i data-lucide="x" class="w-3.5 h-3.5"></i>
            </button>
            <div x-show="open"
                 class="absolute z-20 mt-1 w-full max-h-56 overflow-y-auto bg-white border border-gray-200 rounded-md shadow-lg text-sm">
                <template x-if="filtered.length === 0">
                    <div class="px-3 py-2 text-gray-400">Sin resultados</div>
                </template>
                <template x-for="opt in filtered.slice(0, 100)" :key="opt">
                    <div @click="select(opt)"
                         class="px-3 py-2 cursor-pointer hover:bg-blue-50"
                         :class="{ 'bg-blue-50 font-medium text-blue-700': opt === selected }"
                         x-text="opt"></div>
                </template>
            </div>
        </div>
    @elseif($options !== null)
        <select id="{{ $name }}" name="{{ $name }}"
                class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 py-2 px-2.5 bg-white">
            <option value="">{{ $placeholder ?: '-- Todos --' }}</option>
            @foreach ($options as $option)
                <option value="{{ $option }}" {{ (string) $currentValue === (string) $option ? 'selected' : '' }}>
                    {{ $option }}
                </option>
            @endforeach
        </select>
    @else
        <input type="{{ $type }}" id="{{ $name }}" name="{{ $name }}"
               placeholder="{{ $placeholder }}" value="{{ $currentValue }}"
               class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 py-2 px-2.5">
    @endif
</div>
