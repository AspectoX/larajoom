@props([
    'color' => '',
    'icon' => '',
    'title' => '',
    'number' => '',
    'percent' => null,
    'description' => null,
])

<div class="info-box color-{{ $color }}">
    <span class="info-box-icon">
        @icon($icon)
    </span>
    <div class="info-box-content">
        <span class="info-box-text">{{ $title }}</span>
        <span class="info-box-number">{{ $number }}</span>
        @if (! is_null($percent))
            <div class="progress">
                <div class="progress-bar" style="width: {{ $percent }}%"></div>
            </div>
            <span class="progress-description">{{ $description }}</span>
        @endif
    </div>
</div>
