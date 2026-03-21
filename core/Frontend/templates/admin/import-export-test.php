<div class="row g-3">
    <article class="col-12 col-xl-8">
        <div class="card">
            <div class="card-header"><h3 class="card-title">CSV Import Preview</h3></div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="csvInput" class="form-label">CSV Dosyasi</label>
                    <input type="file" class="form-control" id="csvInput" accept=".csv,text/csv">
                    <small class="text-secondary">Beklenen kolonlar: kod, baslik, tutar, durum, tarih</small>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6 col-lg-4">
                        <label class="form-label" for="mapCode">Kod</label>
                        <select id="mapCode" class="form-select" disabled></select>
                    </div>
                    <div class="col-6 col-lg-4">
                        <label class="form-label" for="mapTitle">Baslik</label>
                        <select id="mapTitle" class="form-select" disabled></select>
                    </div>
                    <div class="col-6 col-lg-4">
                        <label class="form-label" for="mapAmount">Tutar</label>
                        <select id="mapAmount" class="form-select" disabled></select>
                    </div>
                </div>
                <div class="table-responsive border rounded">
                    <table class="table table-vcenter mb-0" id="importPreviewTable">
                        <thead><tr><th>Kod</th><th>Baslik</th><th>Tutar</th><th>Durum</th><th>Tarih</th></tr></thead>
                        <tbody><tr><td colspan="5" class="text-secondary">Dosya secilmedi.</td></tr></tbody>
                    </table>
                </div>
            </div>
        </div>
    </article>

    <article class="col-12 col-xl-4">
        <div class="card">
            <div class="card-header"><h3 class="card-title">CSV Export</h3></div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label" for="exportStatus">Durum Filtresi</label>
                    <select class="form-select" id="exportStatus">
                        <option value="all">Tum Kayitlar</option>
                        <option value="Aktif">Aktif</option>
                        <option value="Taslak">Taslak</option>
                        <option value="Beklemede">Beklemede</option>
                    </select>
                </div>
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-primary" id="exportCsvBtn">CSV Export Indir</button>
                </div>
                <pre id="importExportOutput" class="mt-3 mb-0 p-3 border bg-transparent" style="overflow:auto; max-height:220px;">Henuz islem yapilmadi.</pre>
            </div>
        </div>
    </article>
</div>

<script>
    (() => {
        const csvInput = document.getElementById('csvInput');
        const output = document.getElementById('importExportOutput');
        const previewBody = document.querySelector('#importPreviewTable tbody');
        const exportButton = document.getElementById('exportCsvBtn');
        const exportStatus = document.getElementById('exportStatus');
        const mapCode = document.getElementById('mapCode');
        const mapTitle = document.getElementById('mapTitle');
        const mapAmount = document.getElementById('mapAmount');

        if (!csvInput || !output || !previewBody || !exportButton || !exportStatus || !mapCode || !mapTitle || !mapAmount) {
            return;
        }

        const demoRows = [
            {kod: 'T-001', baslik: 'Mart Paket Teklifi', tutar: '125000', durum: 'Aktif', tarih: '2026-03-21'},
            {kod: 'T-002', baslik: 'Restoran Maliyet Hesabi', tutar: '42000', durum: 'Taslak', tarih: '2026-03-20'},
            {kod: 'T-003', baslik: 'CMS Gelisim Sprinti', tutar: '78000', durum: 'Beklemede', tarih: '2026-03-18'},
        ];

        const renderPreview = (rows) => {
            if (!rows.length) {
                previewBody.innerHTML = '<tr><td colspan="5" class="text-secondary">Gosterilecek satir yok.</td></tr>';
                return;
            }

            previewBody.innerHTML = rows.map((row) => {
                return `<tr>
                  <td>${row.kod ?? ''}</td>
                  <td>${row.baslik ?? ''}</td>
                  <td>${row.tutar ?? ''}</td>
                  <td>${row.durum ?? ''}</td>
                  <td>${row.tarih ?? ''}</td>
                </tr>`;
            }).join('');
        };

        const parseCsv = (text) => {
            const lines = text.split(/\r?\n/).filter((line) => line.trim() !== '');
            if (lines.length < 2) {
                return {headers: [], rows: []};
            }

            const splitLine = (line) => {
                const cells = [];
                let value = '';
                let inQuotes = false;
                for (let i = 0; i < line.length; i++) {
                    const char = line[i];
                    if (char === '"' && line[i + 1] === '"') {
                        value += '"';
                        i++;
                        continue;
                    }
                    if (char === '"') {
                        inQuotes = !inQuotes;
                        continue;
                    }
                    if (char === ',' && !inQuotes) {
                        cells.push(value.trim());
                        value = '';
                        continue;
                    }
                    value += char;
                }
                cells.push(value.trim());
                return cells;
            };

            const headers = splitLine(lines[0]);
            const rows = lines.slice(1).map((line) => splitLine(line));
            return {headers, rows};
        };

        const fillMappings = (headers) => {
            const selects = [mapCode, mapTitle, mapAmount];
            selects.forEach((select) => {
                select.innerHTML = '';
                headers.forEach((header, index) => {
                    const option = document.createElement('option');
                    option.value = String(index);
                    option.textContent = header;
                    select.appendChild(option);
                });
                select.disabled = headers.length === 0;
            });
        };

        const downloadCsv = (rows, filename) => {
            const header = ['kod', 'baslik', 'tutar', 'durum', 'tarih'];
            const lines = [header.join(',')];
            rows.forEach((row) => {
                lines.push([row.kod, row.baslik, row.tutar, row.durum, row.tarih].map((cell) => `"${String(cell ?? '').replace(/"/g, '""')}"`).join(','));
            });
            const blob = new Blob([lines.join('\n')], {type: 'text/csv;charset=utf-8;'});
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = filename;
            document.body.appendChild(link);
            link.click();
            link.remove();
            URL.revokeObjectURL(link.href);
        };

        const safeText = (value) => String(value ?? '').replace(/[<>&]/g, '');

        window.kirpiImportExport = {
            parseCsv,
            exportCsv: downloadCsv,
        };

        csvInput.addEventListener('change', async (event) => {
            const file = event.target.files?.[0];
            if (!file) return;

            const text = await file.text();
            const {headers, rows} = parseCsv(text);
            fillMappings(headers);

            if (headers.length === 0 || rows.length === 0) {
                output.textContent = JSON.stringify({ok: false, message: 'Gecerli satir bulunamadi.'}, null, 2);
                renderPreview([]);
                return;
            }

            const mapped = rows.map((row) => ({
                kod: safeText(row[Number(mapCode.value) || 0]),
                baslik: safeText(row[Number(mapTitle.value) || 1]),
                tutar: safeText(row[Number(mapAmount.value) || 2]),
                durum: safeText(row[3]),
                tarih: safeText(row[4]),
            }));

            renderPreview(mapped.slice(0, 10));
            output.textContent = JSON.stringify({
                ok: true,
                headers,
                total_rows: rows.length,
                preview_rows: Math.min(mapped.length, 10),
            }, null, 2);
        });

        exportButton.addEventListener('click', () => {
            const filter = String(exportStatus.value || 'all');
            const rows = filter === 'all' ? demoRows : demoRows.filter((row) => row.durum === filter);
            downloadCsv(rows, `kirpi-export-${filter}.csv`);
            output.textContent = JSON.stringify({
                ok: true,
                export_filter: filter,
                exported_rows: rows.length,
            }, null, 2);
        });

        renderPreview(demoRows);
    })();
</script>
