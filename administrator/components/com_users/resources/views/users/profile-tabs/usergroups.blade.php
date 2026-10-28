<nav class="nav-permissions">
    <ul class="nav-permissions--list">
        @foreach ($groups as $group)
            @php
                $levelClass = match ((int)$group->id) {
                    1 => '',
                    2, 3 => 'nivel-5',
                    4, 5, 6 => 'nivel-4',
                    7 => 'nivel-3',
                    8 => 'nivel-2',
                    9 => 'nivel-1',
                    default => '',
                };
                $isActive = in_array($group->id, $userGroupIds);
            @endphp
            <li class="nav-permissions--item {{ $levelClass }} {{ $isActive ? 'active' : '' }}">
                <input type="checkbox" name="groups[]" value="{{ $group->id }}" class="form-check" id="group-{{ $group->id }}" @checked($isActive)>
                <label class="nav-permissions--link" for="group-{{ $group->id }}">{{ $group->title }}</label>
            </li>
        @endforeach
    </ul>
</nav>