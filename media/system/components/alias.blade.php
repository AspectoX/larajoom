@props([
    'name' => 'alias',
    'source' => 'title',
    'label' => null,
    'value' => null,
])

<div class="form-group">
    @if ($label)
        <label for="{{ $name }}" class="form-label">{{ $label }}</label>
    @endif
    <input type="text" id="{{ $name }}" name="{{ $name }}" value="{{ old($name, $value) }}" class="form-input" readonly>
</div>

@once
<script>
(function () {
    function toSlug(str) {
        return (str || '').toString().toLowerCase()
            .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
            .replace(/[^a-z0-9\s-]/g, '')
            .trim().replace(/\s+/g, '-').replace(/-+/g, '-');
    }

    function initAlias() {
        var source = document.querySelector('[name="{{ $source }}"]');
        var target = document.querySelector('[name="{{ $name }}"]');

        if (!source || !target) {
            return;
        }

        target.setAttribute('readonly', 'readonly');
        target.value = toSlug(source.value) || target.value;

        source.addEventListener('input', function () {
            target.value = toSlug(source.value);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAlias);
    } else {
        initAlias();
    }
})();
</script>
@endonce
