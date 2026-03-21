<article class="panel">
    <h2>Button</h2>
    <div class="inline">
        <?= $buttonPrimary ?>
        <?= $buttonGhost ?>
    </div>
</article>

<article class="panel">
    <h2>Card</h2>
    <?= $card ?>
</article>

<article class="panel">
    <h2>Form</h2>
    <?= $form ?>
</article>

<article class="panel">
    <h2>Table</h2>
    <?= $table ?>
</article>

<article class="panel">
    <h2>Notification</h2>
    <div class="inline">
        <button class="btn btn-primary" id="notifySuccessBtn" type="button">Success Toast</button>
        <button class="btn btn-ghost" id="notifyInfoBtn" type="button">Info Toast</button>
        <button class="btn btn-ghost" id="notifyWarningBtn" type="button">Warning Toast</button>
        <button class="btn btn-ghost" id="notifyErrorBtn" type="button">Error Toast</button>
    </div>
</article>
<script>
    (() => {
        let bound = false;

        function bindButtons() {
            if (bound) return;
            const notify = window.kirpiNotify;
            if (!notify) return;

            bound = true;

            document.getElementById('notifySuccessBtn')?.addEventListener('click', () => {
                notify.success('Kayit basariyla tamamlandi.', {title: 'Basarili'});
            });

            document.getElementById('notifyInfoBtn')?.addEventListener('click', () => {
                notify.info('Bu islemde yeni veri yok.', {title: 'Bilgi'});
            });

            document.getElementById('notifyWarningBtn')?.addEventListener('click', () => {
                notify.warning('Stok seviyesi kritik sinira yaklasti.', {title: 'Uyari'});
            });

            document.getElementById('notifyErrorBtn')?.addEventListener('click', () => {
                notify.error('Kayit guncellenemedi, tekrar deneyin.', {title: 'Hata', duration: 4500});
            });
        }

        bindButtons();
        window.addEventListener('kirpi:notify-ready', bindButtons, {once: true});
    })();
</script>
