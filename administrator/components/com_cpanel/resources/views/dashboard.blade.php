@php
    use Aspectox\LaraJoom\Core\Positions\PositionRenderer;

    $position = $position ?? 'cpanel';
    $showAddModule = $showAddModule ?? true;
    $moduleTypes = $moduleTypes ?? [];
@endphp

<div class="cpanel-modules">
    {!! PositionRenderer::renderInCards($position) !!}
</div>
