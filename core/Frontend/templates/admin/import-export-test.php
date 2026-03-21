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
                    <div class="col-6 col-lg-4">
                        <label class="form-label" for="mapStatus">Durum</label>
                        <select id="mapStatus" class="form-select" disabled></select>
                    </div>
                    <div class="col-6 col-lg-4">
                        <label class="form-label" for="mapDate">Tarih</label>
                        <select id="mapDate" class="form-select" disabled></select>
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
            <div class="card-header"><h3 class="card-title">Excel Export</h3></div>
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
                <div class="mb-3">
                    <label class="form-label" for="exportFormat">Format</label>
                    <select class="form-select" id="exportFormat">
                        <option value="excel">Excel (.xls)</option>
                        <option value="csv">CSV (.csv)</option>
                    </select>
                </div>
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-primary" id="exportCsvBtn">Export Indir</button>
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
        const exportFormat = document.getElementById('exportFormat');
        const mapCode = document.getElementById('mapCode');
        const mapTitle = document.getElementById('mapTitle');
        const mapAmount = document.getElementById('mapAmount');
        const mapStatus = document.getElementById('mapStatus');
        const mapDate = document.getElementById('mapDate');

        if (!csvInput || !output || !previewBody || !exportButton || !exportStatus || !exportFormat || !mapCode || !mapTitle || !mapAmount || !mapStatus || !mapDate) {
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

        const detectDelimiter = (line) => {
            const candidates = [',', ';', '\t', '|'];
            let best = ',';
            let bestCount = -1;
            candidates.forEach((delimiter) => {
                const count = line.split(delimiter).length;
                if (count > bestCount) {
                    best = delimiter;
                    bestCount = count;
                }
            });

            return best;
        };

        const parseCsv = (text) => {
            const lines = text.split(/\r?\n/).filter((line) => line.trim() !== '');
            if (lines.length < 2) {
                return {headers: [], rows: [], delimiter: ','};
            }

            const delimiter = detectDelimiter(lines[0]);
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
                    if (char === delimiter && !inQuotes) {
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
            return {headers, rows, delimiter};
        };

        const findHeaderIndex = (headers, candidates, fallback) => {
            const normalized = headers.map((header) => String(header || '').toLowerCase().trim());
            for (let i = 0; i < normalized.length; i++) {
                if (candidates.some((candidate) => normalized[i].includes(candidate))) {
                    return i;
                }
            }

            return Math.min(fallback, Math.max(headers.length - 1, 0));
        };

        const fillMappings = (headers) => {
            const selects = [mapCode, mapTitle, mapAmount, mapStatus, mapDate];
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

            if (headers.length > 0) {
                mapCode.value = String(findHeaderIndex(headers, ['kod', 'code'], 0));
                mapTitle.value = String(findHeaderIndex(headers, ['baslik', 'title', 'aciklama'], 1));
                mapAmount.value = String(findHeaderIndex(headers, ['tutar', 'amount', 'fiyat', 'maliyet'], 2));
                mapStatus.value = String(findHeaderIndex(headers, ['durum', 'status'], 3));
                mapDate.value = String(findHeaderIndex(headers, ['tarih', 'date'], 4));
            }
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

        const downloadExcel = (rows, filename) => {
            const safe = (value) => String(value ?? '').replace(/[<>&]/g, '');
            const html = `<!doctype html>
<html>
<head><meta charset="utf-8"></head>
<body>
<table border="1">
  <thead><tr><th>kod</th><th>baslik</th><th>tutar</th><th>durum</th><th>tarih</th></tr></thead>
  <tbody>
    ${rows.map((row) => `<tr><td>${safe(row.kod)}</td><td>${safe(row.baslik)}</td><td>${safe(row.tutar)}</td><td>${safe(row.durum)}</td><td>${safe(row.tarih)}</td></tr>`).join('')}
  </tbody>
</table>
</body>
</html>`;
            const blob = new Blob([html], {type: 'application/vnd.ms-excel;charset=utf-8;'});
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = filename;
            document.body.appendChild(link);
            link.click();
            link.remove();
            URL.revokeObjectURL(link.href);
        };

        const safeText = (value) => String(value ?? '').replace(/[<>&]/g, '');
        const toMappedRows = (rows) => rows.map((row) => ({
            kod: safeText(row[Number(mapCode.value) || 0]),
            baslik: safeText(row[Number(mapTitle.value) || 1]),
            tutar: safeText(row[Number(mapAmount.value) || 2]),
            durum: safeText(row[Number(mapStatus.value) || 3]),
            tarih: safeText(row[Number(mapDate.value) || 4]),
        }));

        let parsedRows = [];
        let parsedHeaders = [];

        window.kirpiImportExport = {
            parseCsv,
            exportCsv: downloadCsv,
            exportExcel: downloadExcel,
        };

        csvInput.addEventListener('change', async (event) => {
            const file = event.target.files?.[0];
            if (!file) return;

            const text = await file.text();
            const {headers, rows, delimiter} = parseCsv(text);
            parsedHeaders = headers;
            parsedRows = rows;
            fillMappings(headers);

            if (headers.length === 0 || rows.length === 0) {
                output.textContent = JSON.stringify({ok: false, message: 'Gecerli satir bulunamadi.'}, null, 2);
                renderPreview([]);
                return;
            }

            const mapped = toMappedRows(rows);

            renderPreview(mapped.slice(0, 10));
            output.textContent = JSON.stringify({
                ok: true,
                headers,
                delimiter,
                total_rows: rows.length,
                preview_rows: Math.min(mapped.length, 10),
            }, null, 2);
        });

        [mapCode, mapTitle, mapAmount, mapStatus, mapDate].forEach((select) => {
            select.addEventListener('change', () => {
                if (!parsedRows.length) return;
                renderPreview(toMappedRows(parsedRows).slice(0, 10));
            });
        });

        exportButton.addEventListener('click', () => {
            const filter = String(exportStatus.value || 'all');
            const format = String(exportFormat.value || 'excel');
            const rows = filter === 'all' ? demoRows : demoRows.filter((row) => row.durum === filter);
            if (format === 'csv') {
                downloadCsv(rows, `kirpi-export-${filter}.csv`);
            } else {
                downloadExcel(rows, `kirpi-export-${filter}.xls`);
            }
            output.textContent = JSON.stringify({
                ok: true,
                export_filter: filter,
                export_format: format,
                exported_rows: rows.length,
            }, null, 2);
        });

        renderPreview(demoRows);
    })();
</script>
