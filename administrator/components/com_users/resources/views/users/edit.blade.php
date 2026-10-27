@extends('admin_tpl::index')

@section('title', __('com_users::com_users.edit_user'))
@section('icon', 'profile')

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
    <x-larajoom::form
        :action="$user->id ? route('larajoom.admin.users.update', $user->id) : route('larajoom.admin.users.store')"
        :method="$user->id ? 'PUT' : 'POST'"
        id="adminForm"
        enctype="multipart/form-data"
    >
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

    <x-larajoom::form :action="route('larajoom.admin.users.addNote', $user->id)" method="POST" id="noteForm">
        <fieldset>
            <legend>{{ __('com_users::com_users.notes') }}</legend>

            <x-larajoom::input name="note_text" :label="__('com_users::com_users.note_text')" required />

            <button type="submit" class="btn">
                @icon('add') {{ __('com_users::com_users.add_note') }}
            </button>
        </fieldset>
    </x-larajoom::form>

    @if ($userNotes->count() > 0)
        <fieldset>
            <legend>{{ __('com_users::com_users.notes_list') }}</legend>
            @foreach ($userNotes as $note)
                <div class="note-item" style="border-bottom: 1px solid #ccc; padding: 10px 0;">
                    <span class="note-text">{{ $note->note_text }}</span>
                    <span class="note-date">{{ \Illuminate\Support\Carbon::parse($note->created_at)->format('Y-m-d H:i') }}</span>
                    <a href="{{ route('larajoom.admin.users.editNote', $note->id) }}" class="btn-table">@icon('edit') {{ __('larajoom_admin::admin.edit') }}</a>
                    <form method="POST" action="{{ route('larajoom.admin.users.destroyNote', $note->id) }}" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn transparent circle">@icon('delete')</button>
                    </form>
                </div>
            @endforeach
        </fieldset>
    @endif
@endsection