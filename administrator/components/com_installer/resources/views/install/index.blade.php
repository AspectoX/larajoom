@extends('tpl::index')

@section('title', __('com_installer::com_installer.install_title'))
@section('icon', 'install')

@section('toolbar')
    <x-larajoom::toolbar :buttons="[
        [
            'label' => __('com_installer::com_installer.back'),
            'icon'  => 'cancel',
            'url'   => route('larajoom.admin.installer.index'),
            'color' => 'warning',
        ],
    ]" />
@endsection

@section('content')
    @if (session('error'))
        <x-message type="danger" :message="session('error')" />
    @endif

    <fieldset>
        <legend>{{ __('com_installer::com_installer.tab_upload') }}</legend>

        <form action="{{ route('larajoom.admin.installer.store') }}" method="post" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="installtype" value="upload">

            <p>
                <label for="install_package">{{ __('com_installer::com_installer.package_file') }}</label>
                <input type="file" id="install_package" name="install_package" accept=".zip" required>
            </p>
            <p>
                <button type="submit" class="ax-btn color-primary">
                    {{ __('com_installer::com_installer.upload_button') }}
                </button>
            </p>
        </form>
    </fieldset>

    <p>{{ __('com_installer::com_installer.types_note') }}</p>
@endsection
