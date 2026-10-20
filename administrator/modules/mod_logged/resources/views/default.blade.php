@php
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Schema;

    // Usuarios con sesión activa en el panel.
    // Si el driver de sesión es de base de datos se usa la tabla de sesiones;
    // si es de archivos se listan los últimos usuarios que entraron.
    $sessionTable = (string) config('session.table', 'sessions');
    $rows = collect();

    if (Schema::hasTable($sessionTable) && Schema::hasTable('larajoom_users')) {
        $rows = DB::table($sessionTable)
            ->join('larajoom_users', 'larajoom_users.id', '=', $sessionTable . '.user_id')
            ->select('larajoom_users.id', 'larajoom_users.name', 'larajoom_users.username', 'larajoom_users.email')
            ->distinct()
            ->get();
    } elseif (Schema::hasTable('larajoom_users')) {
        $rows = DB::table('larajoom_users')
            ->select('larajoom_users.id', 'larajoom_users.name', 'larajoom_users.username', 'larajoom_users.email')
            ->orderByDesc('larajoom_users.id')
            ->limit(10)
            ->get();
    }
@endphp

<table class="ax-table">
    <thead>
        <tr>
            <th>{{ __('mdl::mod_logged.name') }}</th>
            <th>{{ __('mdl::mod_logged.client') }}</th>
            <th>{{ __('mdl::mod_logged.date') }}</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($rows as $row)
            <tr>
                <th scope="row">
                    @if (\Aspectox\LaraJoom\Core\Access::canWrite('com_users'))
                        <a href="{{ route('larajoom.admin.users.edit', $row->id) }}">{{ $row->name }}</a>
                    @else
                        {{ $row->name }}
                    @endif
                </th>
                <td>{{ __('mdl::mod_logged.administrator') }}</td>
                <td>{{ $row->email }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="3">{{ __('mdl::mod_logged.no_users') }}</td>
            </tr>
        @endforelse
    </tbody>
</table>
