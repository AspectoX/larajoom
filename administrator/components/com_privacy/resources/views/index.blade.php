@extends('admin_tpl::index')

@section('title', __('com_privacy::com_privacy.privacy_dashboard'))

@section('toolbar')
    <nav aria-label="Barra de Herramientas">
        <a href="{{ route('larajoom.admin.privacy.requests') }}">{{ __('com_privacy::com_privacy.requests') }}</a>
        <a href="{{ route('larajoom.admin.privacy.consents') }}">{{ __('com_privacy::com_privacy.consents') }}</a>
    </nav>
@endsection

@section('content')
    <h2>{{ __('com_privacy::com_privacy.privacy_dashboard') }}</h2>

    <div style="display:flex;gap:1rem;flex-wrap:wrap;">
        <div style="border:1px solid #ccc;padding:1rem;min-width:160px;">
            <strong>{{ $stats['pending'] }}</strong><br>
            {{ __('com_privacy::com_privacy.requests_pending') }}
        </div>
        <div style="border:1px solid #ccc;padding:1rem;min-width:160px;">
            <strong>{{ $stats['completed'] }}</strong><br>
            {{ __('com_privacy::com_privacy.requests_completed') }}
        </div>
        <div style="border:1px solid #ccc;padding:1rem;min-width:160px;">
            <strong>{{ $stats['consents'] }}</strong><br>
            {{ __('com_privacy::com_privacy.consents_registered') }}
        </div>
    </div>
@endsection
