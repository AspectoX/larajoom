@php
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

$modulePosition = $displayData['modules'] ?? 'position';

// Usuario actual: avatar e inicial
$currentUser = auth('ax-larajoom')->user();
$userName = trim((string) ($currentUser->name ?? '')) ?: 'Invitado';
$userInitial = mb_strtoupper(mb_substr($userName, 0, 1));

$avatarUrl = null;
$avatarValue = trim((string) ($currentUser->avatar ?? ''));

if ($avatarValue !== '') {
    if (preg_match('#^(https?:)?//#i', $avatarValue) || str_starts_with($avatarValue, '/')) {
        $avatarUrl = $avatarValue;
    } else {
        $relative = ltrim($avatarValue, '/');
        $candidates = [
            ['path' => public_path($relative), 'url' => url($relative)],
            ['path' => storage_path('app/public/' . $relative), 'url' => Storage::disk('public')->url($relative)],
            ['path' => public_path('avatars/' . $relative), 'url' => url('avatars/' . $relative)],
            ['path' => storage_path('app/public/avatars/' . $relative), 'url' => Storage::disk('public')->url('avatars/' . $relative)],
        ];

        foreach ($candidates as $candidate) {
            if (is_file($candidate['path'])) {
                $avatarUrl = $candidate['url'];
                break;
            }
        }
    }
}


$moduleHtml = [];
$moduleCollapsedHtml = [];

if (Schema::hasTable('larajoom_modules')) {
    $modules = DB::table('larajoom_modules')
        ->where('position', $modulePosition)
        ->where('published', 1)
        ->orderBy('ordering', 'asc')
        ->get();

    foreach ($modules as $mod) {
        $out = view('modules.' . $mod->module . '::default', ['module' => $mod])->render();

        if ($out !== '') {
            $moduleHtml[] = $out;
            $moduleCollapsedHtml[] = $out;
        }
    }
}
@endphp

<div class="header-items">
    @foreach ($moduleHtml as $mod)
        <div class="header-item">{{ $mod }}</div>
    @endforeach
    <div class="header-item">
        <div class="header-item-content dropdown header-profile">
            <button class="dropdown-toggle" id="header-profile-toggle" data-bs-toggle="dropdown" type="button"
                data-bs-auto-close="outside" aria-haspopup="true" aria-expanded="false" aria-controls="header-profile-menu"
                title="">
                <span class="header-item-img" data-fallback-initial="{{ $userInitial }}">
                    @if ($avatarUrl)
                        <img src="{{ $avatarUrl }}" alt="{{ $userName }}" width="32" height="32">
                    @else
                        <span class="header-item-initial" aria-hidden="true">{{ $userInitial }}</span>
                    @endif
                </span>
                <span class="header-item-text">
                    {{ $userName }}
                </span>
                <span class="icon-angle-down" aria-hidden="true">
                    @icon('angle-down')
                </span>
            </button>
            <div class="dropdown-menu dropdown-menu-end" id="header-profile-menu" aria-labelledby="header-profile-toggle">
                <a class="dropdown-item" href="{{ route('larajoom.admin.profile') }}">Profile</a>
                <form id="logout-form" action="/administrator/logout" method="POST">
                    @csrf
                    <button type="submit" class="dropdown-item">{{ __('tmp::atum.lj_logout') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>


