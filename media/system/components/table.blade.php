@props([
    'headers' => [],
    'rows' => [],
    'actions' => [],
    'draggable' => true,
    'searchable' => true,
    'sortable' => true,
    'tree' => false,
    'ids' => [],
    'meta' => [],
    'reorderUrl' => null,
])

<div class="ax-table-wrapper" data-ax-table data-draggable="{{ $draggable ? '1' : '0' }}" data-tree="{{ $tree ? '1' : '0' }}"
     @if ($reorderUrl) data-reorder-url="{{ $reorderUrl }}" data-csrf="{{ csrf_token() }}" @endif>
    @if ($searchable)
        <div class="navSecond">
            <div class="navSecond-search">
                <input type="text" class="form-input" data-ax-table-search placeholder="{{ __('larajoom_admin::admin.search') }}">
                <button type="button" class="btn circle" data-ax-table-clear>@icon('close')</button>
            </div>
        </div>
    @endif

    <table class="ax-table">
        <thead>
            <tr>
                @if ($draggable)
                    <th style="white-space: nowrap;"></th>
                @endif
                @foreach ($headers as $i => $header)
                    <th @if ($sortable) data-sort @endif style="cursor:pointer;white-space: nowrap;">
                        <span class="th-label">{{ $header }}</span>
                        <span class="th-sort-icon">
                            <span class="sort-arrow sort-arrow-top">▲</span>
                            <span class="sort-arrow sort-arrow-down">▼</span>
                        </span>
                    </th>
                @endforeach
                @if (!empty($actions))
                    <th class="actions">{{ __('larajoom_admin::admin.actions') }}</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $rowIndex => $rowCells)
                @php($rowId = $meta[$rowIndex]['id'] ?? ($ids[$rowIndex] ?? null))
                <tr @if ($draggable) draggable="true" @endif
                    @if ($rowId !== null) data-id="{{ $rowId }}" @endif
                    @if ($tree && isset($meta[$rowIndex])) data-parent="{{ $meta[$rowIndex]['parent'] }}" data-level="{{ $meta[$rowIndex]['level'] }}" @endif>
                    @if ($draggable)
                        <td class="drag-handle">@icon('drag-handle')</td>
                    @endif
                    @foreach ($rowCells as $cell)
                        <td>
                            @if (is_array($cell) && array_key_exists('image', $cell))
                                <img src="{{ $cell['image'] }}" alt="" width="{{ $cell['width'] ?? 60 }}">
                            @elseif (is_array($cell) && array_key_exists('toggle', $cell))
                                <form method="POST" action="{{ $cell['toggle']['url'] }}" style="display:inline">
                                    @csrf
                                    <button type="submit" class="btn transparent circle">
                                        <span class="btn-icon {{ $cell['toggle']['active'] ? 'text-warning' : '' }}">★</span>
                                    </button>
                                </form>
                            @elseif (is_array($cell) && array_key_exists('icon', $cell))
                                <span class="btn-icon {{ $cell['class'] ?? '' }}">{{ $cell['icon'] }}</span>
                            @elseif (is_array($cell) && array_key_exists('html', $cell))
                                {!! $cell['html'] !!}
                            @else
                                {{ $cell }}
                            @endif
                        </td>
                    @endforeach
                    @if (!empty($actions))
                        @php($rowActions = $actions[$rowIndex] ?? [])
                        <td class="actions" style="white-space: nowrap;">
                            @if (isset($rowActions['edit']))
                                <a href="{{ $rowActions['edit'] }}" class="btn transparent circle" title="Editar">
                                    <span class="btn-icon text-info">@icon('edit')</span>
                                </a>
                            @endif

                            @if (isset($rowActions['delete']))
                                <form method="POST" action="{{ $rowActions['delete'] === false ? '' : $rowActions['delete'] }}" style="display:inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn transparent circle" title="Eliminar" @disabled($rowActions['delete'] === false)>
                                        <span class="btn-icon text-danger">@icon('delete')</span>
                                    </button>
                                </form>
                            @endif

                            @if (isset($rowActions['view']))
                                <a href="{{ $rowActions['view'] }}" class="btn transparent circle" title="Ver">
                                    <span class="btn-icon text-info">@icon('view')</span>
                                </a>
                            @endif
                        </td>
                    @endif
                </tr>
            @empty
                <tr data-empty>
                    <td colspan="{{ count($headers) + (!empty($actions) ? 1 : 0) + ($draggable ? 1 : 0) }}">No hay registros.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <script>
    (function () {
        var wrap = document.currentScript.closest('[data-ax-table]');
        if (!wrap) return;

        var tbody = wrap.querySelector('tbody');
        var draggable = wrap.getAttribute('data-draggable') === '1';
        var tree = wrap.getAttribute('data-tree') === '1';
        var url = wrap.getAttribute('data-reorder-url');
        var csrf = wrap.getAttribute('data-csrf') || '';

        function rows() {
            return [].slice.call(tbody.querySelectorAll('tr')).filter(function (tr) {
                return !tr.hasAttribute('data-empty');
            });
        }
        function level(tr) {
            return parseInt(tr.getAttribute('data-level') || '1', 10);
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

        // ===== Arrastrar =====
        if (!draggable) return;

        var dragging = [];
        var rootLevel = 1;

        function subtree(tr) {
            var all = rows(), i = all.indexOf(tr), lv = level(tr), out = [tr];
            for (var j = i + 1; j < all.length; j++) {
                if (level(all[j]) > lv) out.push(all[j]); else break;
            }
            return out;
        }
        function subtreeEnd(tr) {
            var s = subtree(tr);
            return s[s.length - 1];
        }
        function levelOf(id) {
            if (!id || id === '0') return 0;
            var p = tbody.querySelector('tr[data-id="' + id + '"]');
            return p ? level(p) : 0;
        }
        function clearMarks() {
            rows().forEach(function (r) { r.classList.remove('drop-before', 'drop-after', 'drop-inside', 'dragging'); });
        }

        tbody.addEventListener('dragstart', function (e) {
            var tr = e.target.closest('tr');
            if (!tr || tr.hasAttribute('data-empty')) return;
            dragging = tree ? subtree(tr) : [tr];
            rootLevel = level(tr);
            dragging.forEach(function (r) { r.classList.add('dragging'); });
            e.dataTransfer.effectAllowed = 'move';
        });

        tbody.addEventListener('dragover', function (e) {
            if (!dragging.length) return;
            e.preventDefault();
            var tr = e.target.closest('tr');
            rows().forEach(function (r) { r.classList.remove('drop-before', 'drop-after', 'drop-inside'); });
            if (!tr || dragging.indexOf(tr) !== -1 || tr.hasAttribute('data-empty')) return;
            var rect = tr.getBoundingClientRect();
            var rel = (e.clientY - rect.top) / rect.height;
            tr.classList.add(tree ? (rel < 0.3 ? 'drop-before' : rel > 0.7 ? 'drop-after' : 'drop-inside')
                                  : (rel < 0.5 ? 'drop-before' : 'drop-after'));
        });

        tbody.addEventListener('drop', function (e) {
            if (!dragging.length) return;
            e.preventDefault();
            var tr = e.target.closest('tr');
            if (!tr || dragging.indexOf(tr) !== -1) { clearMarks(); dragging = []; return; }

            var root = dragging[0];
            var ref, newParent;

            if (tree && tr.classList.contains('drop-inside')) {
                newParent = tr.getAttribute('data-id');
                ref = subtreeEnd(tr).nextSibling;
            } else if (tr.classList.contains('drop-before')) {
                newParent = tree ? tr.getAttribute('data-parent') : root.getAttribute('data-parent');
                ref = tr;
            } else {
                newParent = tree ? tr.getAttribute('data-parent') : root.getAttribute('data-parent');
                ref = tree ? subtreeEnd(tr).nextSibling : tr.nextSibling;
            }

            dragging.forEach(function (r) { tbody.insertBefore(r, ref); });

            if (tree) {
                var delta = (levelOf(newParent) + 1) - rootLevel;
                dragging.forEach(function (r) { r.setAttribute('data-level', level(r) + delta); });
                root.setAttribute('data-parent', newParent || '0');
            }

            clearMarks();
            dragging = [];
            save();
        });

        tbody.addEventListener('dragend', function () {
            clearMarks();
            dragging = [];
        });

        function save() {
            if (!url) return;
            var body = tree
                ? { nodes: rows().map(function (tr) {
                      return {
                          id: tr.getAttribute('data-id'),
                          parent: tr.getAttribute('data-parent'),
                          level: tr.getAttribute('data-level'),
                      };
                  }) }
                : { order: rows().map(function (tr) { return tr.getAttribute('data-id'); }).filter(Boolean) };

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
                body: JSON.stringify(body),
            }).catch(function () {});
        }
    })();
    </script>
</div>
