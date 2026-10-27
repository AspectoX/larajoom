@extends('admin_tpl::index')

@section('title', __('com_users::com_users.messaging'))
@section('icon', 'message')

@section('toolbar')
    <x-larajoom::toolbar :buttons="[
        [
            'label' => __('com_users::com_users.back_to_users'),
            'icon'  => 'back',
            'url'   => route('larajoom.admin.users.index'),
            'color' => 'warning',
        ],
    ]" />
@endsection

@section('content')
    <x-larajoom::form :action="route('larajoom.admin.users.sendMessage')" method="POST" id="sendMessageForm">
        <fieldset>
            <legend>{{ __('com_users::com_users.send_message') }}</legend>

            <x-larajoom::input name="to_user_id" :label="__('com_users::com_users.to_user')" required />

            <x-larajoom::input name="subject" :label="__('com_users::com_users.subject')" required />

            <x-larajoom::textarea name="message" :label="__('com_users::com_users.message')" required />

            <button type="submit" class="btn">
                @icon('send') {{ __('com_users::com_users.send_message') }}
            </button>
        </fieldset>
    </x-larajoom::form>

    @if ($messages->count() > 0)
        <fieldset>
            <legend>{{ __('com_users::com_users.inbox') }}</legend>
            @foreach ($messages as $msg)
                <div class="message-item" style="border-bottom: 1px solid #ccc; padding: 10px 0;">
                    <strong>{{ $msg->from_name }}</strong> ({{ $msg->from_email }})
                    <br>
                    <strong>{{ $msg->subject }}</strong>
                    <br>
                    {{ $msg->message }}
                    <br>
                    <small>{{ \Illuminate\Support\Carbon::parse($msg->created_at)->format('Y-m-d H:i') }}</small>
                    @if (!$msg->is_read)
                        <span style="color: red;">({{ __('com_users::com_users.unread') }})</span>
                    @else
                        <span style="color: green;">({{ __('com_users::com.users.read') }})</span>
                    @endif
                    <br>
                    <a href="{{ route('larajoom.admin.users.markMessageAsRead', $msg->id) }}" class="btn-table">@icon('check') {{ __('larajoom_admin::admin.mark_read') }}</a>
                    <form method="POST" action="{{ route('larajoom.admin.users.destroyMessage', $msg->id) }}" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn transparent circle">@icon('delete') {{ __('larajoom_admin::admin.delete') }}</button>
                    </form>
                </div>
            @endforeach
        </fieldset>
    @else
        <p>{{ __('com_users::com_users.no_messages') }}</p>
    @endif
@endsection