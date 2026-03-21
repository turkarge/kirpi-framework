<div class="row g-3">
    <article class="col-12">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Button</h3></div>
            <div class="card-body d-flex gap-2 flex-wrap">
                <?= $buttonPrimary ?>
                <?= $buttonGhost ?>
            </div>
        </div>
    </article>

    <article class="col-12 col-xl-6">
        <div class="card h-100">
            <div class="card-header"><h3 class="card-title">Card</h3></div>
            <div class="card-body">
                <?= $card ?>
            </div>
        </div>
    </article>

    <article class="col-12 col-xl-6">
        <div class="card h-100">
            <div class="card-header"><h3 class="card-title">Form</h3></div>
            <div class="card-body">
                <?= $form ?>
            </div>
        </div>
    </article>

    <article class="col-12">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Table</h3></div>
            <div class="card-body p-0">
                <?= $table ?>
            </div>
        </div>
    </article>

    <article class="col-12">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Notification</h3></div>
            <div class="card-body d-flex gap-2 flex-wrap">
                <button class="btn btn-primary" id="notifySuccessBtn" type="button">Success Toast</button>
                <button class="btn btn-outline-secondary" id="notifyInfoBtn" type="button">Info Toast</button>
                <button class="btn btn-outline-secondary" id="notifyWarningBtn" type="button">Warning Toast</button>
                <button class="btn btn-outline-secondary" id="notifyErrorBtn" type="button">Error Toast</button>
            </div>
        </div>
    </article>
</div>
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
