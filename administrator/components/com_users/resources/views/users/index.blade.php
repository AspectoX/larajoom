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
            'label' => __('com_users::com_users.activate'),
            'icon'  => 'circle-check',
            'url'   => route('larajoom.admin.users.bulkActivate'),
            'method'=> 'POST',
            'color' => 'success',
            'disabled' => true,
        ],
        [
            'label' => __('com_users::com_users.block'),
            'icon'  => 'circle-times',
            'url'   => route('larajoom.admin.users.bulkBlock'),
            'method'=> 'POST',
            'color' => 'danger',
            'disabled' => true,
        ],
        [
            'label' => __('com_users::com_users.unblock'),
            'icon'  => 'circle-check',
            'url'   => route('larajoom.admin.users.bulkUnblock'),
            'method'=> 'POST',
            'color' => 'success',
            'disabled' => true,
        ],
        [
            'label' => __('larajoom_admin::admin.delete'),
            'icon'  => 'delete',
            'url'   => route('larajoom.admin.users.bulkDelete'),
            'method'=> 'DELETE',
            'color' => 'danger',
            'disabled' => true,
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
                    <th style="white-space: nowrap;">
                        <input type="checkbox" id="check-all" class="form-check">
                    </th>
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
                    <th>
                        <div class="col-sort">
                            <span class="th-label">{{ __('com_users::com_users.mfa') }}</span>
                            <span class="th-sort-icon">
                                <span class="sort-arrow sort-arrow-top">@icon('sort-top')</span>
                                <span class="sort-arrow sort-arrow-down">@icon('sort-down')</span>
                            </span>
                        </div>
                    </th>
                    <th>
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

    <script>
    (function () {
        var checkAll = document.getElementById('check-all');
        if (checkAll) {
            checkAll.addEventListener('change', function () {
                var checked = checkAll.checked;
                document.querySelectorAll('tbody tr td:first-child input.form-check').forEach(function (cb) {
                    cb.checked = checked;
                });
                updateToolbarButtons();
            });
        }

        var rowCheckboxes = document.querySelectorAll('tbody tr td:first-child input.form-check');
        if (!rowCheckboxes.length) return;

        var toolbar = document.querySelector('.toolbar') || document.body;
        var toolbarButtons = toolbar.querySelectorAll('button');

        var actionTexts = {
            activate: "{{ __('com_users::com_users.activate') }}",
            block: "{{ __('com_users::com_users.block') }}",
            unblock: "{{ __('com_users::com_users.unblock') }}",
            delete: "{{ __('larajoom_admin::admin.delete') }}",
        };

        var actionButtons = {};
        toolbarButtons.forEach(function (btn) {
            var text = btn.textContent.trim();
            if (text === actionTexts.activate) actionButtons.activate = btn;
            if (text === actionTexts.block) actionButtons.block = btn;
            if (text === actionTexts.unblock) actionButtons.unblock = btn;
            if (text === actionTexts.delete) actionButtons.delete = btn;
        });

        function updateToolbarButtons() {
            var anyChecked = document.querySelectorAll('tbody tr td:first-child input.form-check:checked').length > 0;
            Object.values(actionButtons).forEach(function (btn) {
                if (btn) {
                    if (anyChecked) {
                        btn.removeAttribute('disabled');
                    } else {
                        btn.setAttribute('disabled', 'disabled');
                    }
                }
            });
        }

        rowCheckboxes.forEach(function (cb) {
            cb.addEventListener('change', updateToolbarButtons);
        });

        updateToolbarButtons();
    })();
</script>
@endsection
