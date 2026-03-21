<article class="panel">
    <div class="admin-grid">
        <aside class="admin-sidebar">
            <strong>Navigation</strong>
            <a class="admin-nav-link" href="#">Dashboard</a>
            <a class="admin-nav-link" href="#">Teklifler</a>
            <a class="admin-nav-link" href="#">Receteler</a>
            <a class="admin-nav-link" href="#">Icerikler</a>
            <a class="admin-nav-link" href="#">Ayarlar</a>
        </aside>
        <section class="admin-main">
            <header class="admin-topbar">
                <strong>Admin Genel Bakis</strong>
                <div class="inline">
                    <?= $saveButton ?>
                    <?= $filterButton ?>
                </div>
            </header>
            <div class="kpi-grid">
                <?= $kpiCardA ?>
                <?= $kpiCardB ?>
            </div>
            <div class="content-grid">
                <section class="panel">
                    <h2>Hizli Form</h2>
                    <?= $quickForm ?>
                </section>
                <section class="panel">
                    <h2>Son Kayitlar</h2>
                    <?= $latestTable ?>
                </section>
            </div>
        </section>
    </div>
</article>
