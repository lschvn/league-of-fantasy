@props([
    'items' => [],
])

<div class="tabs tabs-bordered">
    @foreach ($items as $item)
        <a href="{{ $item['href'] }}" class="tab {{ !empty($item['active']) ? 'tab-active' : '' }}">
            {{ $item['label'] }}
        </a>
    @endforeach
</div>
