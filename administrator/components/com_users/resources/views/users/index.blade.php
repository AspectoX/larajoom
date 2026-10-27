@extends('admin_tpl::index')

@section('title', __('com_users::com_users.users'))
@section('icon', 'users')

@section('toolbar')
    <x-larajoom::toolbar :buttons="[
        [
            'label' => __('com_users::com_users.new_user'),
            'icon'  => 'add',
            'url'   => route('larajoom.admin.users.create'),
            'color' => 'success',
        ],
        [
            'label' => __('larajoom_admin::admin.edit'),
            'icon'  => 'edit',
            'color' => 'primary',
        ],
        [
            'label' => __('larajoom_admin::admin.delete'),
            'icon'  => 'delete',
            'color' => 'danger',
        ],
        [
            'label' => __('com_users::com_users.messaging'),
            'icon'  => 'message',
            'url'   => route('larajoom.admin.users.messages'),
            'color' => 'info',
        ],
    ]" />
@endsection

@section('content')
    <div class="ax-table-wrapper" data-ax-table data-searchable="1" data-sortable="1">
        <div class="navSecond">
            <div class="navSecond-search">
                <div class="input-button">
                    <input type="text" class="form-input" data-ax-table-search placeholder="{{ __('larajoom_admin::admin.search') }}">
                    <button type="button" class="btn">
                        @icon('search')
                    </button>
                </div>
                <button type="button" class="btn circle" data-ax-table-clear>@icon('close')</button>
            </div>
        </div>

        <table class="ax-table">
            <thead>
                <tr>
                    <th style="white-space: nowrap;"></th>
                    <th data-sort>
                        <div class="col-sort">
                            <span class="th-label">{{ __('com_users::com_users.id') }}</span>
                            <span class="th-sort-icon">
                                <span class="sort-arrow sort-arrow-top">@icon('sort-top')</span>
                                <span class="sort-arrow sort-arrow-down">@icon('sort-down')</span>
                            </span>
                        </div>
                    </th>
                    <th data-sort>
                        <div class="col-sort">
                            <span class="th-label">{{ __('com_users::com_users.user_name') }}</span>
                            <span class="th-sort-icon">
                                <span class="sort-arrow sort-arrow-top">@icon('sort-top')</span>
                                <span class="sort-arrow sort-arrow-down">@icon('sort-down')</span>
                            </span>
                        </div>
                    </th>
                    <th data-sort>
                        <div class="col-sort">
                            <span class="th-label">{{ __('com_users::com_users.username') }}</span>
                            <span class="th-sort-icon">
                                <span class="sort-arrow sort-arrow-top">@icon('sort-top')</span>
                                <span class="sort-arrow sort-arrow-down">@icon('sort-down')</span>
                            </span>
                        </div>
                    </th>
                    <th data-sort>
                        <div class="col-sort">
                            <span class="th-label">{{ __('com_users::com_users.enabled') }}</span>
                            <span class="th-sort-icon">
                                <span class="sort-arrow sort-arrow-top">@icon('sort-top')</span>
                                <span class="sort-arrow sort-arrow-down">@icon('sort-down')</span>
                            </span>
                        </div>
                    </th>
                    <th data-sort>
                        <div class="col-sort">
                            <span class="th-label">{{ __('com_users::com_users.activated') }}</span>
                            <span class="th-sort-icon">
                                <span class="sort-arrow sort-arrow-top">@icon('sort-top')</span>
                                <span class="sort-arrow sort-arrow-down">@icon('sort-down')</span>
                            </span>
                        </div>
                    </th>
                    <th data-sort>
                        <div class="col-sort">
                            <span class="th-label">{{ __('com_users::com_users.mfa') }}</span>
                            <span class="th-sort-icon">
                                <span class="sort-arrow sort-arrow-top">@icon('sort-top')</span>
                                <span class="sort-arrow sort-arrow-down">@icon('sort-down')</span>
                            </span>
                        </div>
                    </th>
                    <th data-sort>
                        <div class="col-sort">
                            <span class="th-label">{{ __('com_users::com_users.usergroups') }}</span>
                            <span class="th-sort-icon">
                                <span class="sort-arrow sort-arrow-top">@icon('sort-top')</span>
                                <span class="sort-arrow sort-arrow-down">@icon('sort-down')</span>
                            </span>
                        </div>
                    </th>
                    <th data-sort>
                        <div class="col-sort">
                            <span class="th-label">{{ __('com_users::com_users.email') }}</span>
                            <span class="th-sort-icon">
                                <span class="sort-arrow sort-arrow-top">@icon('sort-top')</span>
                                <span class="sort-arrow sort-arrow-down">@icon('sort-down')</span>
                            </span>
                        </div>
                    </th>
                    <th data-sort>
                        <div class="col-sort">
                            <span class="th-label">{{ __('com_users::com_users.last_visit') }}</span>
                            <span class="th-sort-icon">
                                <span class="sort-arrow sort-arrow-top">@icon('sort-top')</span>
                                <span class="sort-arrow sort-arrow-down">@icon('sort-down')</span>
                            </span>
                        </div>
                    </th>
                    <th data-sort>
                        <div class="col-sort">
                            <span class="th-label">{{ __('com_users::com_users.registered') }}</span>
                            <span class="th-sort-icon">
                                <span class="sort-arrow sort-arrow-top">@icon('sort-top')</span>
                                <span class="sort-arrow sort-arrow-down">@icon('sort-down')</span>
                            </span>
                        </div>
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    @php
                        $groupNames = collect($userGroups[$user->id] ?? [])
                            ->map(fn ($map) => $groups[$map->group_id]->title ?? null)
                            ->filter()
                            ->implode(', ');
                        $params = is_string($user->params) ? json_decode($user->params, true) : ($user->params ?? []);
                        $mfa = is_array($params) && array_key_exists('mfa', $params) ? 'Sí' : '—';
                        $firstGroupId = !empty($userGroups[$user->id]) ? $userGroups[$user->id]->first()->group_id : null;
                    @endphp
                    <tr>
                        <td><input type="checkbox" class="form-check" name="user[]" value="{{ $user->id }}"></td>
                        <td>{{ $user->id }}</td>
                        <td>
                            <a href="{{ route('larajoom.admin.users.edit', $user->id) }}">{{ $user->name }}</a>
                            <a href="#" class="btn-table">@icon('add') {{ __('com_users::com_users.add_note') }}</a>
                        </td>
                        <td>{{ $user->username }}</td>
                        <td>
                            <form method="POST" action="{{ route('larajoom.admin.users.toggle', $user->id) }}">
                                @csrf
                                <button type="submit" class="btn transparent circle">
                                    <span class="icon-td">
                                        @if ($user->enabled)
                                            @icon('circle-times')
                                        @else
                                            @icon('circle-check')
                                        @endif
                                    </span>
                                </button>
                            </form>
                        </td>
                        <td>
                            <span class="icon-td">
                                @if ($user->activated)
                                    @icon('check')
                                @else
                                    @icon('cancel')
                                @endif
                            </span>
                        </td>
                        <td>
                            {{-- {{ $mfa }} No tengo idea que pone en los demas joomla averigua plis--}}
                            <span class="icon-td text-danger">
                                 @icon('cancel') {{-- para Si --}}
                            </span>
                        </td>
                        <td>{{ $groupNames !== '' ? $groupNames : __('com_users::com_users.no_user_group') }}
                            <a href="{{ $firstGroupId ? route('larajoom.admin.groups.edit', $firstGroupId) : '#' }}" class="btn-table">@icon('permissions') {{ __('com_users::com_users.permissions') }}</a>
                        </td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $lastVisits[$user->id] ?? '—' }}</td>
                        <td>{{ $user->registerDate ?? $user->created_at?->format('Y-m-d') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="pagination">
        {{ $users->links() }}
    </div>

    <x-larajoom::form :action="route('larajoom.admin.users.massMail')" method="POST" id="massMailForm">
        <fieldset>
            <legend>{{ __('com_users::com_users.mass_mail') }}</legend>
            <x-larajoom::input name="subject" :label="__('com_users::com_users.subject')" required />
            <x-larajoom::textarea name="message" :label="__('com_users::com_users.message')" required />
            @foreach ($users as $user)
                <input type="checkbox" name="user_ids[]" value="{{ $user->id }}" class="form-check">
                {{ $user->name }} ({{ $user->email }})<br>
            @endforeach
            <button type="submit" class="btn">
                @icon('mail') {{ __('com_users::com_users.send_mass_mail') }}
            </button>
        </fieldset>
    </x-larajoom::form>

    <a href="{{ route('larajoom.admin.users.messages') }}" class="btn">{{ __('com_users::com_users.messaging') }}</a>
</section>