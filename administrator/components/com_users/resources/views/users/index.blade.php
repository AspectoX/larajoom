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
                    <th data-sort>{{ __('com_users::com_users.id') }}</th>
                    <th data-sort>{{ __('com_users::com_users.user_name') }}</th>
                    <th data-sort>{{ __('com_users::com_users.username') }}</th>
                    <th data-sort>{{ __('com_users::com_users.enabled') }}</th>
                    <th data-sort>{{ __('com_users::com_users.activated') }}</th>
                    <th data-sort>{{ __('com_users::com_users.mfa') }}</th>
                    <th data-sort>{{ __('com_users::com_users.usergroups') }}</th>
                    <th data-sort>{{ __('com_users::com_users.email') }}</th>
                    <th data-sort>{{ __('com_users::com_users.last_visit') }}</th>
                    <th data-sort>{{ __('com_users::com_users.registered') }}</th>
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

    <script>
    (function () {
        var wrap = document.currentScript.closest('[data-ax-table]');
        if (!wrap) return;

        var table = wrap.querySelector('table');
        var tbody = table.querySelector('tbody');

        function rows() {
            return [].slice.call(tbody.querySelectorAll('tr'));
        }

        // ===== Buscar =====
        var input = wrap.querySelector('[data-ax-table-search]');
        var clear = wrap.querySelector('[data-ax-table-clear]');
        function applySearch() {
            var q = (input ? input.value : '').toLowerCase().trim();
            rows().forEach(function (tr) {
                tr.style.display = (!q || tr.textContent.toLowerCase().indexOf(q) !== -1) ? '' : 'none';
            });
        }
        if (input) input.addEventListener('input', applySearch);
        if (clear) clear.addEventListener('click', function () { if (input) { input.value = ''; applySearch(); } });

        // ===== Ordenar =====
        function cellText(tr, col) {
            var td = tr.children[col];
            return td ? td.textContent.trim() : '';
        }
        function sortBy(th, dir) {
            var col = [].indexOf.call(th.parentNode.children, th);
            rows().sort(function (a, b) {
                var av = cellText(a, col), bv = cellText(b, col);
                var an = parseFloat(av.replace(',', '.')), bn = parseFloat(bv.replace(',', '.'));
                var cmp = (!isNaN(an) && !isNaN(bn)) ? (an - bn) : av.localeCompare(bv);
                return dir === 'asc' ? cmp : -cmp;
            }).forEach(function (tr) { tbody.appendChild(tr); });
            wrap.querySelectorAll('th[data-sort]').forEach(function (o) { o.removeAttribute('data-dir'); });
            th.setAttribute('data-dir', dir);
        }
        wrap.querySelectorAll('th[data-sort]').forEach(function (th) {
            th.addEventListener('click', function () {
                sortBy(th, th.getAttribute('data-dir') === 'asc' ? 'desc' : 'asc');
            });
        });
    })();
    </script>
@endsection