<fieldset class="options-form">
    <legend>{{ $label ?? __('com_users::com_users.multifactor_auth') }}</legend>

    @if (isset($twoFactorStatus['enabled']) && $twoFactorStatus['enabled'])
        <p>{{ __('com_users::com_users.two_factor_enabled') }}</p>
        <ul>
            @foreach ($twoFactorStatus['methods'] ?? [] as $method)
                <li>
                    <strong>{{ $method['label'] }}</strong><br>
                    <small>{{ __('com_users::com_users.two_factor_secret') }}: <code>{{ $method['secret'] }}</code></small>
                </li>
            @endforeach
        </ul>
        @if ($user)
            <form method="POST" action="{{ route('larajoom.admin.users.remove2fa', $user->getKey()) }}" style="display: inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn danger">
                    @icon('delete') {{ __('com_users::com_users.disable_two_factor') }}
                </button>
            </form>
        @endif
    @else
        <p>{{ __('com_users::com_users.two_factor_not_enabled') }}</p>
        @if ($user)
            <button type="button" class="btn" onclick="document.getElementById('setup2faForm').submit()">
                @icon('key') {{ __('com_users::com_users.setup_two_factor') }}
            </button>
            <form method="POST" action="{{ route('larajoom.admin.users.setup2fa', $user->getKey()) }}" id="setup2faForm" style="display: none;">
                @csrf
                @method('POST')
                <input type="hidden" name="method" value="totp">
            </form>
        @endif
    @endif
</fieldset>