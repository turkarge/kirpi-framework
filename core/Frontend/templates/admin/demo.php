<section class="admin-grid admin-grid-mono">
    <aside class="admin-sidebar admin-sidebar-dark">
        <div class="admin-brand">KIRPI</div>
        <strong>Navigation</strong>
        <a class="admin-nav-link active" href="#">Dashboard</a>
        <a class="admin-nav-link" href="#">Teklifler</a>
        <a class="admin-nav-link" href="#">Receteler</a>
        <a class="admin-nav-link" href="#">Icerikler</a>
        <a class="admin-nav-link" href="#">Ayarlar</a>
        <div class="admin-side-foot">
            <small>Plan: Growth</small>
            <small>v1.0.0</small>
        </div>
    </aside>
    <section class="admin-main admin-main-mono">
        <header class="admin-topbar admin-topbar-mono">
            <div>
                <strong>Admin Genel Bakis</strong>
                <p class="admin-muted">Teklif, recete ve CMS akislarini tek panelden yonet.</p>
            </div>
            <div class="inline">
                <?= $filterButton ?>
                <?= $saveButton ?>
            </div>
        </header>

        <section class="stat-grid">
            <article class="stat-box">
                <span>Bu Ay Teklif</span>
                <strong>42</strong>
            </article>
            <article class="stat-box">
                <span>Donusum</span>
                <strong>%63</strong>
            </article>
            <article class="stat-box">
                <span>Aktif Musteri</span>
                <strong>18</strong>
            </article>
            <article class="stat-box">
                <span>Bekleyen Is</span>
                <strong>7</strong>
            </article>
        </section>

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
</section>
