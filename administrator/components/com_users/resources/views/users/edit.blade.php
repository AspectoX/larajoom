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
    <x-larajoom::form :action="route('larajoom.admin.users.update', $user->id)" method="PUT" id="adminForm">
        <fieldset>
            <legend>{{ __('com_users::com_users.edit_user') }}</legend>

            <x-larajoom::input name="name" :label="__('com_users::com_users.user_name')" :value="old('name', $user->name)" required />

            <x-larajoom::input name="email" type="email" :label="__('com_users::com_users.email')" :value="old('email', $user->email)" required />

            <x-larajoom::input name="password" type="password" :label="__('com_users::com_users.password')" />

            <fieldset>
                <legend>{{ __('com_users::com_users.usergroups') }}</legend>

                @forelse ($groups as $group)
                    <x-larajoom::toggle
                        name="groups[]"
                        :id="'user_group_' . $group->id"
                        :value="$group->id"
                        :label="$group->title"
                        :checked="in_array((int) $group->id, $userGroupIds)"
                    />
                @empty
                    <p>{{ __('com_users::com_users.no_records_groups') }}</p>
                @endforelse
            </fieldset>
        </fieldset>
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
                <div class="note-item" style="margin-bottom: 10px;">
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
