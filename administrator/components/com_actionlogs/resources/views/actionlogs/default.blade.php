@extends('admin_tpl::index')

@section('title', 'Registro de Acciones')

@section('toolbar')
    <nav aria-label="Barra de Herramientas">
        <a href="{{ route('larajoom.admin.actionlogs.export') }}">Exportar CSV</a>
    </nav>
@endsection

@section('content')
    <h1>Registro de Acciones</h1>

    <table class="ax-table">
        <thead>
            <tr>
                <th>Usuario</th>
                <th>Acción</th>
                <th>Extensión</th>
                <th>Elemento</th>
                <th>IP</th>
                <th>Fecha</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($items as $item)
                <tr>
                    <td>{{ $item->user_id }}</td>
                    <td>{{ $item->action }}</td>
                    <td>{{ $item->extension }}</td>
                    <td>{{ $item->item_id }}</td>
                    <td>{{ $item->ip }}</td>
                    <td>{{ $item->created_at }}</td>
                </tr>
            @empty
                <tr><td colspan="6">No hay registros de acciones.</td></tr>
            @endforelse
        </tbody>
    </table>

    {{ $items->links() }}
@endsection
