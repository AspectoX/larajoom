@extends('admin_tpl::index')

@section('title', __('com_privacy::com_privacy.consents'))

@section('toolbar')
    <nav aria-label="Barra de Herramientas">
        <a href="{{ route('larajoom.admin.privacy.index') }}">← {{ __('com_privacy::com_privacy.dashboard') }}</a>
    </nav>
@endsection

@section('content')
    <h2>{{ __('com_privacy::com_privacy.consents') }}</h2>

    <table style="width:100%;border-collapse:collapse;">
        <thead>
            <tr>
                <th style="text-align:left;">ID</th>
                <th style="text-align:left;">{{ __('com_privacy::com_privacy.user_id') }}</th>
                <th style="text-align:left;">IP</th>
                <th style="text-align:left;">{{ __('com_privacy::com_privacy.created_at') }}</th>
                <th style="text-align:left;">{{ __('com_privacy::com_privacy.details') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($consents as $c)
                <tr>
                    <td>{{ $c->id }}</td>
                    <td>{{ $c->user_id ?? '—' }}</td>
                    <td>{{ $c->ip }}</td>
                    <td>{{ $c->created_at }}</td>
                    <td><code>{{ $c->details }}</code></td>
                </tr>
            @empty
                <tr><td colspan="5">{{ __('com_privacy::com_privacy.no_consents') }}</td></tr>
            @endforelse
        </tbody>
    </table>

    {{ $consents->links() }}
@endsection
