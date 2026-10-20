/**
 * LaraJoom Atum template - sidebar toggle + dropdowns
 */
(function () {
    'use strict';

    function initDropdowns() {
        const toggles = document.querySelectorAll('[data-bs-toggle="dropdown"]');

        toggles.forEach(function (toggle) {
            if (toggle.dataset.ljDropdown === '1') {
                return;
            }
            toggle.dataset.ljDropdown = '1';

            const parent = toggle.closest('.dropdown') || toggle.parentElement;
            const menu = parent ? parent.querySelector('.dropdown-menu') : null;

            if (!menu) {
                return;
            }

            menu.hidden = true;

            const isOpen = function () {
                return toggle.getAttribute('aria-expanded') === 'true';
            };

            const open = function () {
                menu.hidden = false;
                menu.classList.add('show');
                toggle.classList.add('show');
                toggle.setAttribute('aria-expanded', 'true');
            };

            const close = function () {
                menu.classList.remove('show');
                toggle.classList.remove('show');
                toggle.setAttribute('aria-expanded', 'false');
                menu.hidden = true;
            };

            toggle.addEventListener('click', function (event) {
                event.preventDefault();
                event.stopPropagation();

                if (isOpen()) {
                    close();
                } else {
                    document.querySelectorAll('[data-bs-toggle="dropdown"][aria-expanded="true"]').forEach(function (other) {
                        if (other !== toggle) {
                            other.click();
                        }
                    });
                    open();
                }
            });

            menu.addEventListener('click', function (event) {
                if (event.target.closest('.dropdown-item')) {
                    close();
                }
            });

            document.addEventListener('click', function (event) {
                if (isOpen() && !parent.contains(event.target)) {
                    close();
                }
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && isOpen()) {
                    close();
                    toggle.focus();
                }
            });
        });
    }

    function getCookie(name) {
        const row = document.cookie.split('; ').find(r => r.startsWith(name + '='));
        return row ? row.split('=')[1] : '';
    }

    function setCookie(name, value) {
        document.cookie = name + '=' + value + '; path=/; SameSite=Lax';
    }

    function initAvatarFallback() {
        document.querySelectorAll('.header-item-img').forEach(function (wrapper) {
            const initial = wrapper.dataset.fallbackInitial || '';

            wrapper.querySelectorAll('img').forEach(function (img) {
                img.addEventListener('error', function () {
                    if (!initial || wrapper.querySelector('.header-item-initial')) {
                        img.remove();
                        return;
                    }

                    const span = document.createElement('span');
                    span.className = 'header-item-initial';
                    span.setAttribute('aria-hidden', 'true');
                    span.textContent = initial;
                    img.replaceWith(span);
                });
            });
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        initDropdowns();
        initAvatarFallback();

        const wrapper = document.getElementById('wrapper');
        const menuToggle = document.getElementById('menu-collapse');
        const logo = document.querySelector('.logo');

        if (!wrapper || !menuToggle) {
            return;
        }

        function applyState(state) {
            if (state === 'closed') {
                wrapper.classList.add('closed');
            } else {
                wrapper.classList.remove('closed');
            }
            if (logo) {
                logo.classList.toggle('small', state === 'closed');
            }
        }

        // Sync logo with wrapper state (server already set wrapper class from cookie)
        applyState(wrapper.classList.contains('closed') ? 'closed' : 'open');

        menuToggle.addEventListener('click', function (event) {
            event.preventDefault();
            wrapper.classList.toggle('closed');
            const state = wrapper.classList.contains('closed') ? 'closed' : 'open';
            setCookie('atumSidebarState', state);
            if (logo) {
                logo.classList.toggle('small', state === 'closed');
            }
        });
    });
})();
