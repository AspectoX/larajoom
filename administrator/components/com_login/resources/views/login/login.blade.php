@extends('system::layout_login')

@section('content')
    <div class="login_message w-100">
        <div id="system-message-container" aria-live="polite"></div>
    </div>
    <div class="login">
        <div class="main-brand logo text-center">
            <img loading="eager" decoding="async" src="{{ url('media/templates/administrator/atum/images/logos/iso-LaraJoom.png') }}"
                alt="">
        </div>
            <form action="{{ route('larajoom.admin.login.store') }}" method="post" id="form-login" class="form-validate">
                @csrf
                <input type="hidden" name="option" value="com_login">
                <input type="hidden" name="task" value="login">

                <x-larajoom::input name="username" id="mod-login-username" type="text" :label="__('com_login::com_login.username')" :value="old('username')"
                    required autofocus autocomplete="username" />
                <x-larajoom::password name="passwd" id="mod-login-password" :label="__('com_login::com_login.password')" required
                    autocomplete="current-password" />

                <x-larajoom::select id="lang" name="lang" :label="__('com_login::com_login.language')">
                    <button>
                        <selectedcontent></selectedcontent>
                        <span class="icon-svg text-white">
                            <svg data-icon="caret-down" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512"><path fill="currentColor" d="M31.3 192h257.3c17.8 0 26.7 21.5 14.1 34.1L174.1 354.8c-7.8 7.8-20.5 7.8-28.3 0L17.2 226.1C4.6 213.5 13.5 192 31.3 192z"></path></svg>
                        </span>
                    </button>
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

