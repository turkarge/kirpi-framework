<?php

declare(strict_types=1);
?>
<div class="row g-3">
    <div class="col-12 col-lg-4">
        <div class="card h-100">
            <div class="card-header"><h3 class="card-title">AI SQL Ayarlari</h3></div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-5">Feature</dt>
                    <dd class="col-7"><span class="badge <?= $enabled ? 'bg-green-lt' : 'bg-red-lt' ?>"><?= $enabled ? 'enabled' : 'disabled' ?></span></dd>
                    <dt class="col-5">Provider</dt>
                    <dd class="col-7"><?= htmlspecialchars((string) $provider, ENT_QUOTES, 'UTF-8') ?></dd>
                    <dt class="col-5">Model</dt>
                    <dd class="col-7"><?= htmlspecialchars((string) $model, ENT_QUOTES, 'UTF-8') ?></dd>
                </dl>
                <div class="alert alert-info mt-3 mb-0">
                    Bu test sadece SELECT sorgularina izin verir. SQL guard aktif.
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-8">
        <div class="card h-100">
            <div class="card-header"><h3 class="card-title">Soru Sor</h3></div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label" for="aiSqlModel">Model</label>
                    <select class="form-select" id="aiSqlModel">
                        <?php foreach (($models ?? []) as $item): ?>
                            <option value="<?= htmlspecialchars((string) $item, ENT_QUOTES, 'UTF-8') ?>" <?= (string) $item === (string) $model ? 'selected' : '' ?>>
                                <?= htmlspecialchars((string) $item, ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                        <?php if (empty($models ?? [])): ?>
                            <option value="<?= htmlspecialchars((string) $model, ENT_QUOTES, 'UTF-8') ?>" selected><?= htmlspecialchars((string) $model, ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="aiSqlQuestion">Soru</label>
                    <textarea class="form-control" id="aiSqlQuestion" rows="3" placeholder="Orn: Son 10 bildirimi tarihe gore listele"></textarea>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-primary" id="aiSqlAskBtn">Sorgu Uret ve Calistir</button>
                    <button type="button" class="btn btn-1" id="aiSqlClearBtn">Temizle</button>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Sonuc</h3></div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Uretilen SQL</label>
                    <pre class="p-3 border rounded mb-0" id="aiSqlGenerated">Henuz sorgu uretilmedi.</pre>
                </div>
                <div class="mb-3">
                    <label class="form-label">AI Ozeti</label>
                    <div class="border rounded p-3 text-secondary" id="aiSqlSummary">-</div>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-vcenter card-table">
                        <thead><tr id="aiSqlHeadRow"><th>Sonuc</th></tr></thead>
                        <tbody id="aiSqlBodyRows"><tr><td>Henuz veri yok.</td></tr></tbody>
                    </table>
                </div>
                <pre class="mt-3 mb-0 p-3 border rounded" id="aiSqlOutput">Henuz aksiyon yok.</pre>
            </div>
        </div>
    </div>
</div>

<script>
(() => {
  const questionEl = document.getElementById('aiSqlQuestion');
  const askBtn = document.getElementById('aiSqlAskBtn');
  const clearBtn = document.getElementById('aiSqlClearBtn');
  const modelEl = document.getElementById('aiSqlModel');
  const sqlEl = document.getElementById('aiSqlGenerated');
  const summaryEl = document.getElementById('aiSqlSummary');
  const headEl = document.getElementById('aiSqlHeadRow');
  const bodyEl = document.getElementById('aiSqlBodyRows');
  const outputEl = document.getElementById('aiSqlOutput');

  if (!questionEl || !askBtn || !sqlEl || !summaryEl || !headEl || !bodyEl || !outputEl || !modelEl) return;

  const escapeHtml = (value) => String(value ?? '')
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;');

  const renderRows = (rows) => {
    if (!Array.isArray(rows) || rows.length === 0) {
      headEl.innerHTML = '<th>Sonuc</th>';
      bodyEl.innerHTML = '<tr><td>Kayit bulunamadi.</td></tr>';
      return;
    }

    const keys = Object.keys(rows[0] || {});
    headEl.innerHTML = keys.map((key) => `<th>${escapeHtml(key)}</th>`).join('');
    bodyEl.innerHTML = rows.slice(0, 20).map((row) => {
      const cols = keys.map((key) => `<td>${escapeHtml(row[key])}</td>`).join('');
      return `<tr>${cols}</tr>`;
    }).join('');
  };

  const writeOutput = (payload) => {
    outputEl.textContent = JSON.stringify(payload, null, 2);
  };

  askBtn.addEventListener('click', async () => {
    const question = String(questionEl.value || '').trim();
    const model = String(modelEl.value || '').trim();
    if (!question) {
      if (window.kirpiNotify) {
        window.kirpiNotify.warning('Soru alani bos olamaz.', { title: 'AI SQL Test' });
      }
      return;
    }

    askBtn.disabled = true;
    askBtn.textContent = 'Calisiyor...';

    try {
      const query = new URLSearchParams({ question, model }).toString();
      const response = await fetch('/kirpi/api/ai-sql-ask?' + query, {
        headers: {
          'Accept': 'application/json',
        },
      });
      const payload = await response.json();
      writeOutput(payload);

      if (!response.ok || !payload.ok) {
        sqlEl.textContent = '-';
        summaryEl.textContent = payload.error || 'Bilinmeyen hata';
        renderRows([]);
        if (window.kirpiNotify) {
          window.kirpiNotify.error(payload.error || 'AI SQL istegi basarisiz', { title: 'AI SQL Test' });
        }
        return;
      }

      const data = payload.data || {};
      sqlEl.textContent = String(data.sql || '-');
      summaryEl.textContent = String(data.summary || '-');
      renderRows(data.rows || []);
      if (window.kirpiNotify) {
        window.kirpiNotify.success('Sorgu calisti. Satir: ' + String(data.row_count ?? 0), { title: 'AI SQL Test' });
      }
    } catch (error) {
      const message = error instanceof Error ? error.message : 'Unknown error';
      writeOutput({ ok: false, error: message });
      summaryEl.textContent = message;
      if (window.kirpiNotify) {
        window.kirpiNotify.error(message, { title: 'AI SQL Test' });
      }
    } finally {
      askBtn.disabled = false;
      askBtn.textContent = 'Sorgu Uret ve Calistir';
    }
  });

  clearBtn?.addEventListener('click', () => {
    questionEl.value = '';
    sqlEl.textContent = 'Henuz sorgu uretilmedi.';
    summaryEl.textContent = '-';
    renderRows([]);
    writeOutput({ ok: true, action: 'clear' });
  });
})();
</script>
