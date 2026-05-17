@props(['items'])
{{-- items: [['label' => 'Inicio', 'url' => '/'], ['label' => 'Madrid', 'url' => '/localidades/madrid'], ...] --}}

@php
    $schema = [
        '@context'        => 'https://schema.org',
        '@type'           => 'BreadcrumbList',
        'itemListElement' => collect($items)->map(fn($item, $i) => array_filter([
            '@type'    => 'ListItem',
            'position' => $i + 1,
            'name'     => $item['label'],
            'item'     => isset($item['url']) ? url($item['url']) : null,
        ]))->values()->all(),
    ];
@endphp

<nav aria-label="Breadcrumb" class="breadcrumb-nav">
    <ol class="breadcrumb-list" itemscope itemtype="https://schema.org/BreadcrumbList">
        @foreach($items as $i => $item)
            <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                @if(! $loop->last)
                    <a href="{{ $item['url'] }}" itemprop="item">
                        <span itemprop="name">{{ $item['label'] }}</span>
                    </a>
                    <span aria-hidden="true" class="breadcrumb-sep">›</span>
                @else
                    <span itemprop="name" aria-current="page">{{ $item['label'] }}</span>
                @endif
                <meta itemprop="position" content="{{ $i + 1 }}">
            </li>
        @endforeach
    </ol>
</nav>

<script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
