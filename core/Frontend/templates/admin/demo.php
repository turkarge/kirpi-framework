<article class="panel">
    <style>
        .admin-grid {
            display: grid;
            grid-template-columns: 240px 1fr;
            gap: 14px;
        }
        .admin-sidebar {
            border: 1px solid var(--line);
            border-radius: 12px;
            background: #fff;
            padding: 12px;
            display: grid;
            gap: 8px;
            align-content: start;
        }
        .admin-nav-link {
            display: block;
            text-decoration: none;
            color: var(--ink);
            border: 1px solid var(--line);
            border-radius: 10px;
            padding: 8px 10px;
            font-size: 14px;
        }
        .admin-nav-link:hover { border-color: var(--brand); color: var(--brand); }
        .admin-main { display: grid; gap: 12px; }
        .admin-topbar {
            border: 1px solid var(--line);
            border-radius: 12px;
            background: #fff;
            padding: 10px 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        .kpi-grid {
            display: grid;
            gap: 10px;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        }
        .content-grid {
            display: grid;
            gap: 10px;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            align-items: start;
        }
        @media (max-width: 900px) {
            .admin-grid { grid-template-columns: 1fr; }
        }
    </style>
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
