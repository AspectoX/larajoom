@php
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Uri;

$module = $displayData['module'] ?? null;
$params = $displayData['params'] ?? [];

if (!$module || (string) ($module->content ?? '') === '') {
    return;
}

$id = $module->id ?? 0;

$user = Auth::user();
$canEdit   = false;
$canChange = false;

$moduleTag      = $params['module_tag'] ?? 'div';
$bootstrapSize  = (int) ($params['bootstrap_size'] ?? 6);
$moduleClass    = $bootstrapSize ? 'col-md-' . $bootstrapSize : 'col-md-12';
$headerTag      = $params['header_tag'] ?? 'h2';
$moduleClassSfx = $params['moduleclass_sfx'] ?? '';

$headerClass = $params['header_class'] ? ' ' . htmlspecialchars($params['header_class']) : '';
@endphp

<div class="{{ $moduleClass }} module-wrapper">
    <{{ $moduleTag }} class="card pt-3{{ $moduleClassSfx }}">
        @if ($canEdit || $canChange)
            @php
            $dropdownPosition = 'end';
            @endphp
            <div class="module-actions dropdown">
                <button type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="btn" id="dropdownMenuButton-{{ $id }}">
                    <span class="icon-cogs" aria-hidden="true"></span>
                    <span class="visually-hidden">{{ __('tmp::atum.lj_edit_module', ['title' => $module->title ?? '']) }}</span>
                </button>
                <div class="dropdown-menu dropdown-menu-{{ $dropdownPosition }}" aria-labelledby="dropdownMenuButton-{{ $id }}">
                    @if ($canEdit)
                        @php
                        $url = route('larajoom.admin.modules.edit', ['id' => $id]);
                        @endphp
                        <a class="dropdown-item" href="{{ $url }}">{{ __('tmp::atum.lj_action_edit') }}</a>
                    @endif
                    @if ($canChange)
                        <button type="button" class="dropdown-item unpublish-module" data-module-id="{{ $id }}">{{ __('tmp::atum.lj_action_unpublish') }}</button>
                    @endif
                </div>
            </div>
        @endif
        @if ($module->showtitle ?? false)
            <{{ $headerTag }} class="card-header{{ $headerClass }}">{{ $module->title }}</{{ $headerTag }}>
        @endif
        <div class="module-body">
            {!! $module->content !!}
        </div>
    </{{ $moduleTag }}>
</div>
