@props([
    'name',
    'label',
    'icon' => null,
    'type' => 'text',
    'placeholder' => '',
    'options' => null,
    'value' => null,
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

    @if($options !== null)
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
