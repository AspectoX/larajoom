@props(['id', 'tabs' => []])

<div class="details">
    @foreach ($tabs as $tab)
        <details class="details__item" {{ ($tab['active'] ?? $loop->first) ? 'open' : '' }} name="{{ $id }}">
            <summary class="details__link">{{ $tab['label'] }}</summary>
            <div class="details__body">
                @if (isset($tab['view']))
                    @include($tab['view'], $tab['data'] ?? [])
                @elseif (isset($tab['content']))
                    {{ $tab['content'] }}
                @endif
            </div>
        </details>
    @endforeach
</div>
