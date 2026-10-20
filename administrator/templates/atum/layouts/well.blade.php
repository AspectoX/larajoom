@php
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

$module  = $displayData['module'] ?? null;
$params  = $displayData['params'] ?? [];
$attribs = $displayData['attribs'] ?? [];

if (!$module || (string) ($module->content ?? '') === '') {
    return;
}

$id = $module->id ?? 0;

$user = Auth::user();
$canEdit   = false;
$canChange = false;

$moduleTag      = $params['module_tag'] ?? 'div';
$bootstrapSize  = (int) ($params['bootstrap_size'] ?? 12);
$moduleClass    = $bootstrapSize ? 'col-md-' . $bootstrapSize : 'col-md-12';
$headerTag      = $params['header_tag'] ?? 'h2';
$moduleClassSfx = $params['moduleclass_sfx'] ?? '';

if (!empty($attribs['class'])) {
    $moduleClass .= ' ' . htmlspecialchars($attribs['class']);
}

$headerClass = $params['header_class'] ? ' class="' . htmlspecialchars($params['header_class']) . '"' : '';
$headerIcon = $params['header_icon'] ? '<span class="' . htmlspecialchars($params['header_icon']) . '" aria-hidden="true"></span>' : '';
@endphp

<div class="{{ $moduleClass }} module-wrapper">
    <{{ $moduleTag }} class="card mb-3 {{ $moduleClassSfx }}">
        @if ($canEdit || $canChange || $headerIcon || ($module->showtitle ?? false))
            <div class="card-header">
                @if ($canEdit || $canChange)
                    @php
                    $dropdownPosition = 'end';
                    @endphp
                    <div class="module-actions dropdown">
                        <button type="button" data-bs-toggle="dropdown" data-bs-display="static" aria-haspopup="true" aria-expanded="false" class="btn" id="dropdownMenuButton-{{ $id }}">
                            <span class="icon-cogs" aria-hidden="true"></span>
                            <span class="visually-hidden">{{ __('tmp::atum.lj_edit_module', ['title' => $module->title ?? '']) }}</span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-{{ $dropdownPosition }}" aria-labelledby="dropdownMenuButton-{{ $id }}">
                            @if ($canEdit)
                                @php
                                $url = Route::getFacadeRoot() ? route('larajoom.admin.modules.edit', ['id' => $id]) : '#';
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
                    <{{ $headerTag }}{{ $headerClass }}>
                        {{ $headerIcon }}
                        {{ htmlspecialchars($module->title ?? '') }}
                    </{{ $headerTag }}>
                @endif
            </div>
        @endif
        <div class="card-body">
            {!! $module->content !!}
        </div>
    </{{ $moduleTag }}>
</div>
