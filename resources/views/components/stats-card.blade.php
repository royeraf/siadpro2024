@props([
    'icon',
    'id' => null,
    'value',
    'title',
])

<div class="row">
    <div class="col-12">
        <div class="inline-flex max-[575px]:flex max-[575px]:w-full items-center gap-[15px] mb-[15px]
                    px-[18px] py-3 rounded-lg text-white bg-gradient-to-br from-blue-600 to-blue-700
                    shadow-[0_3px_6px_rgba(0,0,0,0.1)]">
            <div class="text-white/90">
                <i data-lucide="{{ $icon }}" class="w-8 h-8"></i>
            </div>
            <div>
                <span @if($id) id="{{ $id }}-total" @endif
                      class="block text-2xl font-bold leading-[1.1] text-yellow-400">{{ is_numeric($value) ? number_format($value) : $value }}</span>
                <span class="text-[13px] uppercase tracking-[0.5px] opacity-95">{{ $title }}</span>
            </div>
        </div>
    </div>
</div>
