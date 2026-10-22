@php
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Schema;

    // Usuarios con sesión activa en el panel.
    // Si el driver de sesión es de base de datos se usa la tabla de sesiones;
    // si es de archivos se listan los últimos usuarios que entraron.
    $sessionTable = (string) config('session.table', 'sessions');
    $rows = collect();

    if (Schema::hasTable($sessionTable) && Schema::hasTable('larajoom_users')) {
        $rows = DB::table($sessionTable . ' as s')
            ->join('larajoom_users as u', 'u.id', '=', 's.user_id')
            ->selectRaw('u.id, u.name, u.username, u.email, MAX(s.last_activity) as last_activity')
            ->groupBy('u.id', 'u.name', 'u.username', 'u.email')
            ->orderByDesc('last_activity')
            ->limit(5)
            ->get();
    } elseif (Schema::hasTable('larajoom_users')) {
        $rows = DB::table('larajoom_users')
            ->select('id', 'name', 'username', 'email')
            ->orderByDesc('id')
            ->limit(5)
            ->get();
    }
@endphp

<table class="table ax-table" id="logged{{ $module->id ?? '' }}">
    <caption hidden>{{ $title ?? '' }}</caption>
    <thead>
        <tr>
            <th scope="col" class="w-50">{{ __('mdl::mod_logged.name') }}</th>
            <th scope="col" class="w-30">{{ __('mdl::mod_logged.client') }}</th>
            <th scope="col" class="w-20">{{ __('mdl::mod_logged.date') }}</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($rows as $row)
            <tr>
                <th scope="row">
                    @if (\Aspectox\LaraJoom\Core\Access::canWrite('com_users'))
                        <a href="{{ route('larajoom.admin.users.edit', $row->id) }}" title="{{ __('tmp::atum.lj_action_edit') }} {{ $row->name }}">{{ $row->name }}</a>
                    @else
                        {{ $row->name }}
                    @endif
                </th>
                <td>{{ __('mdl::mod_logged.administrator') }}</td>
                <td>@if (!empty($row->last_activity)){{ \Carbon\Carbon::parse($row->last_activity)->format('d-m-Y H:i') }}@else—@endif</td>
            </tr>
        @empty
            <tr>
                <td colspan="3">{{ __('mdl::mod_logged.no_users') }}</td>
            </tr>
        @endforelse
    </tbody>
</table>
