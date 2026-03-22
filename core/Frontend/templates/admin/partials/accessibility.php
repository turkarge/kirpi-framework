<div class="kirpi-sr-only" id="kirpiA11yHint" aria-live="polite">
  Kirpi klavye kisayollari: Alt+1 Dashboard, Alt+2 UI Kit, Alt+3 Notify Test, Alt+4 State Test, Ctrl+K yardim.
</div>
<script>
    (() => {
        const shortcuts = {
            'Alt+1': '/kirpi/admin-demo',
            'Alt+2': '/kirpi/ui-kit',
            'Alt+3': '/kirpi/notify-test',
            'Alt+4': '/kirpi/state-test',
            'Alt+5': '/kirpi/a11y-test',
        };

        const toShortcut = (event) => {
            const keys = [];
            if (event.altKey) keys.push('Alt');
            if (event.ctrlKey) keys.push('Ctrl');
            if (event.shiftKey) keys.push('Shift');

            const key = String(event.key || '').toUpperCase();
            if (key.length === 1 && /^[A-Z0-9]$/.test(key)) {
                keys.push(key);
            } else if (key === 'ESCAPE') {
                keys.push('Escape');
            } else if (key === 'SLASH') {
                keys.push('/');
            }

            return keys.join('+');
        };

        const inEditable = () => {
            const active = document.activeElement;
            if (!active) return false;
            if (active instanceof HTMLInputElement || active instanceof HTMLTextAreaElement || active instanceof HTMLSelectElement) {
                return true;
            }
            return active instanceof HTMLElement && active.isContentEditable;
        };

        const openHelp = () => {
            const helpText = [
                'Alt+1: Dashboard',
                'Alt+2: UI Kit',
                'Alt+3: Notify Test',
                'Alt+4: State Test',
                'Alt+5: A11y Test',
            ].join('<br>');

            if (window.kirpiModal) {
                window.kirpiModal.show({
                    title: 'Klavye Kisayollari',
                    bodyHtml: `<div class="text-secondary">${helpText}</div>`,
                    confirmText: 'Kapat',
                    hideCancel: true,
                });
                return;
            }

            if (window.kirpiNotify) {
                window.kirpiNotify.info('Kisayollar: Alt+1/2/3/4/5', {title: 'A11y'});
            }
        };

        document.addEventListener('keydown', (event) => {
            if (inEditable() && !(event.ctrlKey && (event.key === 'k' || event.key === 'K'))) {
                return;
            }

            const shortcut = toShortcut(event);
            if ((event.ctrlKey || event.metaKey) && (event.key === 'k' || event.key === 'K')) {
                event.preventDefault();
                openHelp();
                window.dispatchEvent(new CustomEvent('kirpi:shortcut', {detail: {shortcut: 'Ctrl+K', action: 'help'}}));
                return;
            }

            const target = shortcuts[shortcut];
            if (!target) return;
            event.preventDefault();
            window.dispatchEvent(new CustomEvent('kirpi:shortcut', {detail: {shortcut, target}}));
            window.location.href = target;
        });

        document.addEventListener('mousedown', () => {
            document.body.classList.remove('keyboard-focus');
        });
        document.addEventListener('keydown', () => {
            document.body.classList.add('keyboard-focus');
        });

        window.kirpiA11y = {
            shortcuts,
            openHelp,
        };
    })();
</script>
