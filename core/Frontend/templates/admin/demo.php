<div class="row g-3">
    <aside class="col-12 col-xl-3">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Navigation</h3>
            </div>
            <div class="list-group list-group-flush">
                <a class="list-group-item list-group-item-action active" href="#">Dashboard</a>
                <a class="list-group-item list-group-item-action" href="#">Teklifler</a>
                <a class="list-group-item list-group-item-action" href="#">Receteler</a>
                <a class="list-group-item list-group-item-action" href="#">Icerikler</a>
                <a class="list-group-item list-group-item-action" href="#">Ayarlar</a>
            </div>
            <div class="card-footer text-secondary">Plan: Growth</div>
        </div>
    </aside>

    <section class="col-12 col-xl-9 d-grid gap-3">
        <div class="card">
            <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <strong class="d-block">Admin Genel Bakis</strong>
                    <span class="text-secondary">Teklif, recete ve CMS akislarini tek panelden yonet.</span>
                </div>
                <div class="d-flex gap-2">
                    <?= $filterButton ?>
                    <?= $saveButton ?>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-6 col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="text-secondary text-uppercase small">Bu Ay Teklif</div>
                        <div class="fs-2 fw-bold">42</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="text-secondary text-uppercase small">Donusum</div>
                        <div class="fs-2 fw-bold">%63</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="text-secondary text-uppercase small">Aktif Musteri</div>
                        <div class="fs-2 fw-bold">18</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="text-secondary text-uppercase small">Bekleyen Is</div>
                        <div class="fs-2 fw-bold">7</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-12 col-lg-6"><?= $kpiCardA ?></div>
            <div class="col-12 col-lg-6"><?= $kpiCardB ?></div>
        </div>

        <div class="row g-3">
            <section class="col-12 col-lg-6">
                <div class="card h-100">
                    <div class="card-header"><h3 class="card-title">Hizli Form</h3></div>
                    <div class="card-body">
                        <?= $quickForm ?>
                    </div>
                </div>
            </section>
            <section class="col-12 col-lg-6">
                <div class="card h-100">
                    <div class="card-header"><h3 class="card-title">Son Kayitlar</h3></div>
                    <div class="card-body p-0">
                        <?= $latestTable ?>
                    </div>
                </div>
            </section>
        </div>
    </section>
</div>
