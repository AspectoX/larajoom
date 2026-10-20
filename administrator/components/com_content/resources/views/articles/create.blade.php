@extends('admin_tpl::index')

@section('title', __('larajoom_admin::admin.new_article'))

@section('toolbar')
    <nav aria-label="Barra de Herramientas">
        <a href="{{ route('larajoom.admin.articles.index') }}">← {{ __('larajoom_admin::admin.cancel') }}</a>
    </nav>
@endsection

@section('content')
    <h2>{{ __('larajoom_admin::admin.new_article') }}</h2>

    <x-larajoom::form :action="route('larajoom.admin.articles.store')" method="POST">
        <fieldset>
            <legend>{{ __('larajoom_admin::admin.new_article') }}</legend>

            <x-larajoom::input 
                name="title" 
                :label="__('larajoom_admin::admin.title')" 
                required 
                size="60" 
            />

            <x-larajoom::alias :label="__('larajoom_admin::admin.alias')" />

            <p>
                <label for="catid">{{ __('larajoom_admin::admin.categories') }}:</label><br>
                <select id="catid" name="catid" required>
                    <option value="0">- Sin Categoría -</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('catid') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->title }}
                        </option>
                    @endforeach
                </select>
            </p>

            <x-larajoom::checkbox name="published" :label="__('larajoom_admin::admin.published')" :checked="true" />

            <p>
                <label for="introtext">Texto de Introducción:</label><br>
                <textarea id="introtext" name="introtext" rows="5" cols="80">{{ old('introtext') }}</textarea>
            </p>

            <p>
                <label for="fulltext">Texto Completo:</label><br>
                <textarea id="fulltext" name="fulltext" rows="12" cols="80">{{ old('fulltext') }}</textarea>
            </p>

            <p>
                <button type="submit">{{ __('larajoom_admin::admin.save') }}</button>
            </p>
        </fieldset>
    </x-larajoom::form>
@endsection
