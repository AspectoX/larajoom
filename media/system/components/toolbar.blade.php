@props([
    'align' => '',
    'buttons' => [],
])

<div class="panelbar {{ $align }}">
    <ul class="toolbar-button-list">
        @foreach ($buttons as $button)
            <li class="toolbar-button-item">
                @if (($button['type'] ?? 'link') === 'submit')
                    <button type="submit"
                            class="btn-joomla btn-{{ $button['color'] ?? 'success' }}"
                            @if (!empty($button['form'])) form="{{ $button['form'] }}" @endif
                            @if (!empty($button['name'])) name="{{ $button['name'] }}" value="{{ $button['value'] ?? '' }}" @endif
                            @disabled(!empty($button['disabled']))>
                        @if (!empty($button['icon']))
                            <div class="icon-joomla">@icon($button['icon'])</div>
                        @endif
                        {{ $button['label'] ?? '' }}
                    </button>
                @else
                    <a href="{{ $button['url'] ?? '#' }}" class="btn-joomla btn-{{ $button['color'] ?? 'success' }}">
                        @if (!empty($button['icon']))
                            <div class="icon-joomla">@icon($button['icon'])</div>
                        @endif
                        {{ $button['label'] ?? '' }}
                    </a>
                @endif
            </li>
        @endforeach
    </ul>
</div>
