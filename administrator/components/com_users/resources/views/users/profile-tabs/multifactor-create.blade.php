<div id="com-users-methods-list">
    <div id="com-users-methods-reset-container" class="d-flex align-items-center border border-1 rounded-3 p-2">
        <div id="com-users-methods-reset-message" class="flex-grow-1">
            {{ __('com_users::com_users.mfa_not_enabled') }}
        </div>
    </div>

    <div id="com-users-methods-list-container">
        <div class="com-users-methods-list-method com-users-methods-list-method-name-totp ">
            <div class="com-users-methods-list-method-header">
                <div class="com-users-methods-list-method-image">
                    <img src="{{ asset('media/plg_multifactorauth_totp/images/totp.svg') }}" alt="Verification code" class="img-fluid">
                </div>
                <div class="com-users-methods-list-method-title">
                    <h3>
                        <span class="me-1 flex-grow-1">
                            {{ __('com_users::com_users.mfa_method_totp') }}
                        </span>
                    </h3>
                </div>
            </div>

            <div class="com-users-methods-list-method-records-container">
                <div class="com-users-methods-list-method-info">
                    {{ __('com_users::com_users.mfa_method_totp_desc') }}
                </div>

                <div class="com-users-methods-list-method-addnew-container border-top pt-2">
                    <a class="com-users-methods-list-method-addnew btn btn-primary btn-sm">
                        <span class="icon-plus-2" aria-hidden="true"></span>
                        {{ __('com_users::com_users.mfa_add_new', ['method' => __('com_users::com_users.mfa_method_totp')]) }}
                    </a>
                </div>
            </div>
        </div>

        <div class="com-users-methods-list-method com-users-methods-list-method-name-yubikey ">
            <div class="com-users-methods-list-method-header">
                <div class="com-users-methods-list-method-image">
                    <img src="{{ asset('media/plg_multifactorauth_yubikey/images/yubikey.svg') }}" alt="YubiKey" class="img-fluid">
                </div>
                <div class="com-users-methods-list-method-title">
                    <h3>
                        <span class="me-1 flex-grow-1">
                            {{ __('com_users::com_users.mfa_method_yubikey') }}
                        </span>
                    </h3>
                </div>
            </div>

            <div class="com-users-methods-list-method-records-container">
                <div class="com-users-methods-list-method-info">
                    {{ __('com_users::com_users.mfa_method_yubikey_desc') }}
                </div>

                <div class="com-users-methods-list-method-addnew-container border-top pt-2">
                    <a class="com-users-methods-list-method-addnew btn btn-primary btn-sm">
                        <span class="icon-plus-2" aria-hidden="true"></span>
                        {{ __('com_users::com_users.mfa_add_new', ['method' => __('com_users::com_users.mfa_method_yubikey')]) }}
                    </a>
                </div>
            </div>
        </div>

        <div class="com-users-methods-list-method com-users-methods-list-method-name-webauthn ">
            <div class="com-users-methods-list-method-header">
                <div class="com-users-methods-list-method-image">
                    <img src="{{ asset('media/plg_multifactorauth_webauthn/images/passkeys.svg') }}" alt="Passkey" class="img-fluid">
                </div>
                <div class="com-users-methods-list-method-title">
                    <h3>
                        <span class="me-1 flex-grow-1">
                            {{ __('com_users::com_users.mfa_method_webauthn') }}
                        </span>
                    </h3>
                </div>
            </div>

            <div class="com-users-methods-list-method-records-container">
                <div class="com-users-methods-list-method-info">
                    {{ __('com_users::com_users.mfa_method_webauthn_desc') }}
                </div>

                <div class="com-users-methods-list-method-addnew-container border-top pt-2">
                    <a class="com-users-methods-list-method-addnew btn btn-primary btn-sm">
                        <span class="icon-plus-2" aria-hidden="true"></span>
                        {{ __('com_users::com_users.mfa_add_new', ['method' => __('com_users::com_users.mfa_method_webauthn')]) }}
                    </a>
                </div>
            </div>
        </div>

        <div class="com-users-methods-list-method com-users-methods-list-method-name-email ">
            <div class="com-users-methods-list-method-header">
                <div class="com-users-methods-list-method-image">
                    <img src="{{ asset('media/plg_multifactorauth_email/images/email.svg') }}" alt="Code by Email" class="img-fluid">
                </div>
                <div class="com-users-methods-list-method-title">
                    <h3>
                        <span class="me-1 flex-grow-1">
                            {{ __('com_users::com_users.mfa_method_email') }}
                        </span>
                    </h3>
                </div>
            </div>

            <div class="com-users-methods-list-method-records-container">
                <div class="com-users-methods-list-method-info">
                    {{ __('com_users::com_users.mfa_method_email_desc') }}
                </div>

                <div class="com-users-methods-list-method-addnew-container border-top pt-2">
                    <a class="com-users-methods-list-method-addnew btn btn-primary btn-sm">
                        <span class="icon-plus-2" aria-hidden="true"></span>
                        {{ __('com_users::com_users.mfa_add_new', ['method' => __('com_users::com_users.mfa_method_email')]) }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>