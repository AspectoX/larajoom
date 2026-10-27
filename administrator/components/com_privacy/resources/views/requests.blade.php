@extends('admin_tpl::index')

@section('title', __('com_privacy::com_privacy.requests'))

@section('toolbar')
    <nav aria-label="Barra de Herramientas">
        <a href="{{ route('larajoom.admin.privacy.index') }}">← {{ __('com_privacy::com_privacy.dashboard') }}</a>
    </nav>
@endsection

@section('content')
    <h2>{{ __('com_privacy::com_privacy.requests') }}</h2>

    <table style="width:100%;border-collapse:collapse;">
        <thead>
            <tr>
                <th style="text-align:left;">ID</th>
                <th style="text-align:left;">{{ __('com_privacy::com_privacy.email') }}</th>
                <th style="text-align:left;">{{ __('com_privacy::com_privacy.type') }}</th>
                <th style="text-align:left;">{{ __('com_privacy::com_privacy.status') }}</th>
                <th style="text-align:left;">{{ __('com_privacy::com_privacy.requested_at') }}</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($requests as $r)
                <tr>
                    <td>{{ $r->id }}</td>
                    <td>{{ $r->email }}</td>
                    <td>{{ $r->type }}</td>
                    <td>{{ $r->status }}</td>
                    <td>{{ $r->requested_at }}</td>
                    <td>
                        <a href="{{ route('larajoom.admin.privacy.export', $r->id) }}">{{ __('com_privacy::com_privacy.export') }}</a>
                        @if ($r->status === 'pending')
                            <form method="POST" action="{{ route('larajoom.admin.privacy.complete', $r->id) }}" style="display:inline;">
                                @csrf
                                <button type="submit">{{ __('com_privacy::com_privacy.complete') }}</button>
                            </form>
                        @endif
                        <form method="POST" action="{{ route('larajoom.admin.privacy.destroy', $r->id) }}" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit">{{ __('com_privacy::com_privacy.delete') }}</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6">{{ __('com_privacy::com_privacy.no_requests') }}</td></tr>
            @endforelse
        </tbody>
    </table>

    {{ $requests->links() }}
@endsection
