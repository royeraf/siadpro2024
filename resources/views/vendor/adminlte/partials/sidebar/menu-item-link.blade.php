<li @if(isset($item['id'])) id="{{ $item['id'] }}" @endif class="nav-item">

    <a class="nav-link {{ $item['class'] }} @if(isset($item['shift'])) {{ $item['shift'] }} @endif"
       href="{{ $item['href'] }}" @if(isset($item['target'])) target="{{ $item['target'] }}" @endif
       {!! $item['data-compiled'] ?? '' !!}>

        <i class="{{ $item['icon'] ?? 'far fa-fw fa-circle' }} {{
            isset($item['icon_color']) ? 'text-'.$item['icon_color'] : ''
        }}"></i>

        <p>
            {{ $item['text'] }}

            @if(isset($item['label']))
                <span class="badge badge-{{ $item['label_color'] ?? 'primary' }} right">
                    {{ $item['label'] }}
                </span>
            @endif{{--
            @if ($item['text'] === 'USUARIOS')
                <span class="badge badge-danger right"> Aqui esta para colocar sobre los datos de los labels al costado de los menus
                    {{ $count }}
                </span>
            @endif --}}
        </p>

    </a>

</li>