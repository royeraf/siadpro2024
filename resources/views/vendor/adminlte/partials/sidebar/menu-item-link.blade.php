<li @if(isset($item['id'])) id="{{ $item['id'] }}" @endif class="nav-item">

    <a class="nav-link {{ $item['class'] }} @if(isset($item['shift'])) {{ $item['shift'] }} @endif"
       href="{{ $item['href'] }}" @if(isset($item['target'])) target="{{ $item['target'] }}" @endif
       {!! $item['data-compiled'] ?? '' !!}>

        @php
            $iconValue = $item['icon'] ?? 'far fa-fw fa-circle';
            $iconColorClass = isset($item['icon_color']) ? 'text-'.$item['icon_color'] : '';
            $isLucideIcon = !str_contains($iconValue, ' ');
        @endphp
        @if($isLucideIcon)
            <i data-lucide="{{ $iconValue }}" class="nav-icon-lucide {{ $iconColorClass }}"></i>
        @else
            <i class="{{ $iconValue }} {{ $iconColorClass }}"></i>
        @endif

        <p>
            {{ $item['text'] }}

            @if(isset($item['label']))
                <span class="badge badge-{{ $item['label_color'] ?? 'primary' }} right">
                    {{ $item['label'] }}
                </span>
            @endif
        </p>

    </a>

</li>