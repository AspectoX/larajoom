@php
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Schema;

    $pending = 0;
    $completed = 0;
    $consents = 0;

    if (Schema::hasTable('larajoom_privacy_requests')) {
        $pending = DB::table('larajoom_privacy_requests')->where('status', 'pending')->count();
        $completed = DB::table('larajoom_privacy_requests')->where('status', 'completed')->count();
    }

    if (Schema::hasTable('larajoom_privacy_consents')) {
        $consents = DB::table('larajoom_privacy_consents')->count();
    }
@endphp

<div class="mod-privacy-dashboard">
    <h4>{{ __('mod_privacy_dashboard::mod_privacy_dashboard.privacy_status') }}</h4>
    <ul style="list-style:none;padding:0;">
        @if ($params['show_requests'] ?? true)
            <li>{{ __('mod_privacy_dashboard::mod_privacy_dashboard.pending') }}: <strong>{{ $pending }}</strong></li>
            <li>{{ __('mod_privacy_dashboard::mod_privacy_dashboard.completed') }}: <strong>{{ $completed }}</strong></li>
        @endif
        @if ($params['show_consents'] ?? true)
            <li>{{ __('mod_privacy_dashboard::mod_privacy_dashboard.consents') }}: <strong>{{ $consents }}</strong></li>
        @endif
    </ul>
    <a href="{{ route('larajoom.admin.privacy.index') }}">
        {{ __('mod_privacy_dashboard::mod_privacy_dashboard.go_to_privacy') }}
    </a>
</div>
