@php
    use Aspectox\LaraJoom\Core\Positions\PositionRenderer;

    $position = $position ?? 'cpanel';
    $showAddModule = $showAddModule ?? true;
    $moduleTypes = $moduleTypes ?? [];
@endphp

<div class="cpanel-modules">
    {!! PositionRenderer::renderInCards($position) !!}
</div>

@if ($showAddModule && \Illuminate\Support\Facades\Auth::guard('ax-larajoom')->check())
    @php
        $canManageModules = \Aspectox\LaraJoom\Core\Access::canWrite('com_modules');
    @endphp

    @if ($canManageModules)
        <div class="module-wrapper">
            <div class="card">
                <button type="button" class="cpanel-add-module ax-btn color-primary"
                    data-cpanel-dialog="cpanel-add-module-dialog">
                    <span class="icon" aria-hidden="true">@icon('add')</span>
                    <span>{{ __('com_cpanel::com_cpanel.add_module') }}</span>
                </button>
            </div>
        </div>

        <dialog id="cpanel-add-module-dialog">
            <header>
                <h3>{{ __('com_cpanel::com_cpanel.add_module') }}</h3>
            </header>
            <nav aria-label="{{ __('com_cpanel::com_cpanel.add_module') }}">
                <ul class="list-unstyled">
                    @foreach ($moduleTypes as $type)
                        <li>
                            <a href="{{ route('larajoom.admin.modules.create', ['position' => $position]) }}">
                                <span class="icon" aria-hidden="true">@icon('module')</span>
                                {{ $type }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </nav>
            <footer>
                <a href="{{ route('larajoom.admin.modules.index') }}" class="ax-btn">
                    {{ __('com_cpanel::com_cpanel.manage_modules') }}
                </a>
                <button type="button" class="ax-btn" data-cpanel-close="cpanel-add-module-dialog">
                    {{ __('larajoom_admin::admin.cancel') }}
                </button>
            </footer>
        </dialog>
    @endif
@endif

<script>
    (function () {
        'use strict';

        function init() {
            document.querySelectorAll('[data-cpanel-dialog]').forEach(function (button) {
                button.addEventListener('click', function () {
                    var dialog = document.getElementById(button.dataset.cpanelDialog);

                    if (dialog && 'showModal' in dialog) {
                        dialog.showModal();
                    }
                });
            });

            document.querySelectorAll('[data-cpanel-close]').forEach(function (button) {
                button.addEventListener('click', function () {
                    var dialog = document.getElementById(button.dataset.cpanelClose);

                    if (dialog && 'close' in dialog) {
                        dialog.close();
                    }
                });
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init);
        } else {
            init();
        }
    })();
</script>
