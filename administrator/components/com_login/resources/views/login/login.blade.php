@extends('system::layout_login')

@section('content')
    <div class="login_message w-100">
        <div id="system-message-container" aria-live="polite"></div>
    </div>
    <div class="login">
        <div class="main-brand logo text-center">
            <img loading="eager" decoding="async" src="media/templates/administrator/atum/images/logos/iso-LaraJoom.png"
                alt="">
        </div>
            <form action="{{ route('larajoom.admin.login.store') }}" method="post" id="form-login" class="form-validate">
            @csrf
            <input type="hidden" name="option" value="com_login">
            <input type="hidden" name="task" value="login">

            <x-larajoom::input name="username" id="mod-login-username" type="text" :label="__('com_login::com_login.username')" :value="old('username')"
                required autofocus autocomplete="username" />
            <div class="input-button">
                <x-larajoom::input name="passwd" id="mod-login-password" type="password" :label="__('com_login::com_login.password')" required
                    autocomplete="current-password" />
                <button type="button" class="btn color-primary input-password-toggle">
                    <span class="icon" aria-hidden="true">
                        @icon('view')
                    </span>
                </button>
            </div>

            <x-larajoom::select id="lang" name="lang" class="form-select" :label="__('com_login::com_login.language')">
                <option value="" selected="selected">{{ __('com_login::com_login.language_default') }}</option>
                <option value="en-GB">{{ __('com_login::com_login.language_en_gb') }}</option>
                <option value="es-ES">{{ __('com_login::com_login.language_es_es') }}</option>
            </x-larajoom::select>
            <div class="form-group">
                <button type="submit" id="btn-login-submit" class="ax-btn color-primary full">
                    {{ __('com_login::com_login.login_button') }}
                </button>
            </div>
            </form>
    </div>
@endsection

