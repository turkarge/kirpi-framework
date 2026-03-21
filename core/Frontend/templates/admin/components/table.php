<div class="ui-table-wrap">
    <table class="ui-table">
        <thead>
            <tr>
                <th>Kod</th>
                <th>Baslik</th>
                <th>Durum</th>
                <th>Tarih</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>T-001</td>
                <td>Mart Teklif Paketi</td>
                <td><span class="tag ok">aktif</span></td>
                <td>2026-03-21</td>
            </tr>
            <tr>
                <td>T-002</td>
                <td>Restoran Maliyet Hesabi</td>
                <td><span class="tag">taslak</span></td>
                <td>2026-03-20</td>
            </tr>
        </tbody>
    </table>
</div>
<style>
    .ui-table-wrap { overflow: auto; }
    .ui-table { width: 100%; border-collapse: collapse; min-width: 520px; }
    .ui-table th, .ui-table td { text-align: left; padding: 10px 8px; border-bottom: 1px solid var(--line); }
    .ui-table th { color: var(--muted); font-size: 13px; text-transform: uppercase; letter-spacing: .03em; }
    .tag {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 999px;
        border: 1px solid var(--line);
        font-size: 12px;
        text-transform: uppercase;
    }
    .tag.ok { border-color: #9ae6b4; color: #22543d; background: #f0fff4; }
</style>
