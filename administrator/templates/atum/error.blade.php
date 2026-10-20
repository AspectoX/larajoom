@php
use Illuminate\Support\Facades\Auth;
$user = Auth::user();
$isGuest = !$user || ($user->guest ?? false);
@endphp

@if ($isGuest)
    @include('atum.error_login')
@else
    @include('atum.error_full')
@endif
