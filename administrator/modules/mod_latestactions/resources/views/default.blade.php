@php
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Schema;

    $limit = (int) ($module->params ?? 0);
    $limit = $limit > 0 ? $limit : 10;

    $items = collect();

    if (Schema::hasTable('larajoom_action_logs')) {
        $items = DB::table('larajoom_action_logs')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }
@endphp

<table class="ax-table">
    <thead>
        <tr>
            <th>{{ __('mdl::mod_latestactions.action') }}</th>
            <th>{{ __('mdl::mod_latestactions.date') }}</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($items as $item)
            <tr>
                <td>
                    {{ __('mdl::mod_latestactions.action_' . $item->action) }}
                    @if ($item->extension)
                        <span class="text-muted">({{ $item->extension }}@if ($item->item_id)-{{ $item->item_id }}@endif)</span>
                    @endif
                </td>
                <td>{{ $item->created_at }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="2">{{ __('mdl::mod_latestactions.no_results') }}</td>
            </tr>
        @endforelse
    </tbody>
</table>
