@extends('admin_tpl::index')

@section('title', __('com_users::com_users.new_user'))
@section('icon', 'add')

@section('toolbar')
    <x-larajoom::toolbar :buttons="[
        [
            'label' => __('larajoom_admin::admin.save'),
            'icon'  => 'save',
            'type'  => 'submit',
            'form'  => 'adminForm',
            'name'  => 'task',
            'value' => 'save',
        ],
        [
            'label' => __('larajoom_admin::admin.cancel'),
            'icon'  => 'cancel',
            'url'   => route('larajoom.admin.users.index'),
            'color' => 'warning',
        ],
    ]" />
@endsection

@section('content')
    <x-larajoom::form :action="route('larajoom.admin.users.store')" method="POST" id="adminForm" enctype="multipart/form-data">
        @csrf
        <div class="details">
            @foreach ($fieldsets as $fieldset)
                <details class="details__item" @if ($loop->first) open @endif name="profile">
                    <summary class="details__link">{{ $fieldset['label'] }}</summary>
                    <div class="details__body">
                        <fieldset class="options-form">
                            <legend>{{ $fieldset['label'] }}</legend>

                            @foreach ($fieldset['fields'] ?? [] as $field)
                                @if ($field['type'] === 'password')
                                    <x-larajoom::password
                                        :name="$field['name']"
                                        :label="$field['label']"
                                        :required="$field['required'] ?? false"
                                        :autocomplete="$field['autocomplete'] ?? 'new-password'" />
                                @elseif ($field['type'] === 'file')
                                    <x-larajoom::file
                                        :name="$field['name']"
                                        :label="$field['label']"
                                        :preview="$field['preview'] ?? null"
                                        accept="image/*" />
                                @elseif ($field['type'] === 'toggle')
                                    <x-larajoom::toggle
                                        :name="$field['name']"
                                        :label="$field['label']"
                                        :checked="$field['value'] ?? false" />
                                @elseif ($field['readonly'] ?? false)
                                    <div class="form-group">
                                        <label class="form-label">{{ $field['label'] }}</label>
                                        <div class="form-input">{{ $field['value'] }}</div>
                                    </div>
                                @else
                                    <x-larajoom::input
                                        :name="$field['name']"
                                        :type="$field['type'] ?? 'text'"
                                        :label="$field['label']"
                                        :value="old($field['name'], $field['value'] ?? null)"
                                        :required="$field['required'] ?? false" />
                                @endif

                                @if (!empty($field['hint']))
                                    <p><small>{{ $field['hint'] }}</small></p>
                                @endif
                            @endforeach

                            @if (!empty($fieldset['message']))
                                <p>{{ $fieldset['message'] }}</p>
                            @endif
                        </fieldset>
                    </div>
                </details>
            @endforeach
        </div>
    </x-larajoom::form>
@endsection