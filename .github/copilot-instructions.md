# Kirpi Framework — Claude Geliştirici Rehberi

Bu dosya Claude Code'un Kirpi Framework projelerinde doğru kod üretmesi için gereken tüm kuralları içerir.

---

## Proje Yapısı

```
kirpi-framework/
├── bootstrap/          # Uygulama başlatma (app.php, helpers.php)
├── config/             # Yapılandırma dosyaları (database, app, auth)
├── core/               # Framework çekirdeği (Router, Controller, Model, DB)
├── database/           # Migration ve seed dosyaları
├── docker/             # Docker yapılandırması
├── manager/            # CLI araçları
├── modules/            # Uygulama modülleri (her modül kendi MVC'sine sahip)
│   └── {modul}/
│       ├── Controllers/
│       ├── Models/
│       └── views/
│           ├── index.php
│           ├── create.php
│           ├── edit.php
│           └── show.php
└── public/             # Web kökü (index.php, assets)
```

---

## Temel Kurallar

### PHP
- Her view dosyası `defined('BASEPATH') or die('Doğrudan erişim yasak');` ile başlar
- Değişkenler her zaman `<?= htmlspecialchars($var) ?>` ile escape edilir
- URL'ler `BASE_URL . '/modul/eylem'` formatında yazılır
- Yetki kontrolü: `has_permission('modul.eylem')` veya `$user['role']`
- **Asla** `die()`, `var_dump()`, `print_r()` production koduna bırakma

### JavaScript
- **Asla** `alert()`, `confirm()`, `prompt()` kullanma → Toastr ve Bootstrap Modal kullan
- JS blokları IIFE pattern ile sarılır: `(function () { 'use strict'; ... })();`
- Event listener'lar `DOMContentLoaded` beklenmeden doğrudan yazılabilir (Tabler bunu halleder)
- Global fonksiyonlar için `window.fnAdi = function() {}` kullan

### AJAX
```javascript
// Standart fetch pattern
fetch(`${BASE_URL}/api/modul/eylem`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload)
})
.then(r => r.json())
.then(data => {
    if (data.status === 'success') {
        toastr.success(data.message);
    } else {
        toastr.error(data.message);
    }
})
.catch(() => toastr.error('Sunucu hatası oluştu.'));
```

---

## UI Teknoloji Yığını (CDN)

```html
<!-- Tabler CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@latest/dist/css/tabler.min.css">

<!-- Toastr CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

<!-- Tabler JS (body kapanmadan önce) -->
<script src="https://cdn.jsdelivr.net/npm/@tabler/core@latest/dist/js/tabler.min.js"></script>

<!-- Toastr JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
```

**İkonlar:** `<i class="ti ti-{icon-name}"></i>` — Tabler Icons, CDN ile otomatik gelir, ek link gerekmez.

---

## Tabler UI Kuralları

### Sayfa İskeleti (Her view dosyası bu yapıyı izler)

```php
<?php defined('BASEPATH') or die('Doğrudan erişim yasak'); ?>

<div class="page-header d-print-none">
  <div class="container-xl">
    <div class="row g-2 align-items-center">
      <div class="col">
        <div class="page-pretitle">Modül Adı</div>
        <h2 class="page-title">Sayfa Başlığı</h2>
      </div>
      <div class="col-auto ms-auto d-print-none">
        <a href="<?= BASE_URL ?>/modul/create" class="btn btn-primary">
          <i class="ti ti-plus me-1"></i> Yeni Ekle
        </a>
      </div>
    </div>
  </div>
</div>

<div class="page-body">
  <div class="container-xl">
    <!-- İçerik buraya -->
  </div>
</div>
```

### Renk Kuralları

CSS değişkeni kullan, hex kodu yazma:

```css
/* ✅ Doğru */
color: var(--tblr-primary);
background: var(--tblr-success-lt);

/* ❌ Yanlış */
color: #0054a6;
background: #d3f9d8;
```

Durum renkleri:
- `bg-success` / `bg-success-lt` → Aktif, Onaylı, Başarılı
- `bg-danger` / `bg-danger-lt` → Hata, Reddedildi, Silindi
- `bg-warning` / `bg-warning-lt` → Beklemede, Uyarı
- `bg-info` / `bg-info-lt` → Bilgi, Taslak
- `bg-secondary` / `bg-secondary-lt` → Pasif, Arşiv

### Toastr Konfigürasyonu (layout'ta bir kez)

```javascript
toastr.options = {
    positionClass: "toast-top-right",
    timeOut: "4000",
    progressBar: true,
    closeButton: true
};
```

---

## CRUD Sayfaları

### Index (Liste) Sayfası Şablonu

```php
<?php defined('BASEPATH') or die(); ?>

<div class="page-header d-print-none">
  <div class="container-xl">
    <div class="row g-2 align-items-center">
      <div class="col">
        <div class="page-pretitle">Yönetim</div>
        <h2 class="page-title">Kayıtlar</h2>
      </div>
      <div class="col-auto ms-auto">
        <div class="d-flex gap-2">
          <div class="input-group">
            <input type="text" id="searchInput" class="form-control" placeholder="Ara...">
            <button class="btn btn-outline-secondary"><i class="ti ti-search"></i></button>
          </div>
          <a href="<?= BASE_URL ?>/modul/create" class="btn btn-primary">
            <i class="ti ti-plus me-1"></i> Yeni
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="page-body">
  <div class="container-xl">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Toplam: <span class="badge bg-primary ms-1"><?= count($records) ?></span></h3>
      </div>

      <?php if (empty($records)): ?>
        <div class="card-body">
          <div class="empty">
            <div class="empty-icon"><i class="ti ti-inbox" style="font-size:3rem;color:var(--tblr-muted)"></i></div>
            <p class="empty-title">Kayıt bulunamadı</p>
            <p class="empty-subtitle text-secondary">Henüz hiç kayıt eklenmemiş.</p>
            <div class="empty-action">
              <a href="<?= BASE_URL ?>/modul/create" class="btn btn-primary">
                <i class="ti ti-plus me-1"></i> İlk Kaydı Ekle
              </a>
            </div>
          </div>
        </div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-vcenter table-nowrap card-table" id="mainTable">
            <thead>
              <tr>
                <th class="w-1"><input class="form-check-input m-0" type="checkbox" id="checkAll"></th>
                <th>Ad</th>
                <th>Durum</th>
                <th>Tarih</th>
                <th class="w-1">İşlem</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($records as $row): ?>
              <tr>
                <td><input class="form-check-input m-0" type="checkbox" value="<?= $row['id'] ?>" name="ids[]"></td>
                <td>
                  <div class="d-flex align-items-center">
                    <span class="avatar avatar-sm bg-primary-lt text-primary me-2">
                      <?= strtoupper(substr($row['name'], 0, 2)) ?>
                    </span>
                    <div>
                      <div class="fw-semibold"><?= htmlspecialchars($row['name']) ?></div>
                      <div class="text-secondary small"><?= htmlspecialchars($row['email'] ?? '') ?></div>
                    </div>
                  </div>
                </td>
                <td>
                  <span class="badge <?= $row['status'] == 1 ? 'bg-success-lt text-success' : 'bg-danger-lt text-danger' ?>">
                    <?= $row['status'] == 1 ? 'Aktif' : 'Pasif' ?>
                  </span>
                </td>
                <td class="text-secondary"><?= date('d.m.Y H:i', strtotime($row['created_at'])) ?></td>
                <td>
                  <div class="dropdown">
                    <button class="btn btn-sm btn-ghost-secondary" data-bs-toggle="dropdown">
                      <i class="ti ti-dots-vertical"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                      <li><a class="dropdown-item" href="<?= BASE_URL ?>/modul/show/<?= $row['id'] ?>">
                        <i class="ti ti-eye me-2 text-secondary"></i> Görüntüle</a></li>
                      <li><a class="dropdown-item" href="<?= BASE_URL ?>/modul/edit/<?= $row['id'] ?>">
                        <i class="ti ti-edit me-2 text-secondary"></i> Düzenle</a></li>
                      <li><hr class="dropdown-divider"></li>
                      <li><a class="dropdown-item text-danger" href="#"
                             onclick="confirmDelete(<?= $row['id'] ?>, '<?= htmlspecialchars($row['name']) ?>')">
                        <i class="ti ti-trash me-2"></i> Sil</a></li>
                    </ul>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Silme Onay Modalı -->
<div class="modal modal-blur fade" id="deleteModal" tabindex="-1">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-body">
        <div class="modal-title">Emin misiniz?</div>
        <div><span id="deleteName" class="fw-bold"></span> kaydı silinecek.</div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">İptal</button>
        <button class="btn btn-danger" id="confirmDeleteBtn">
          <i class="ti ti-trash me-1"></i> Evet, Sil
        </button>
        <input type="hidden" id="deleteId">
      </div>
    </div>
  </div>
</div>

<script>
(function () {
    'use strict';

    document.getElementById('checkAll')?.addEventListener('change', function () {
        document.querySelectorAll('input[name="ids[]"]').forEach(cb => cb.checked = this.checked);
    });

    document.getElementById('searchInput')?.addEventListener('input', function () {
        const q = this.value.toLowerCase();
        document.querySelectorAll('#mainTable tbody tr').forEach(row => {
            row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
    });

    window.confirmDelete = function (id, name) {
        document.getElementById('deleteId').value = id;
        document.getElementById('deleteName').textContent = name;
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    };

    document.getElementById('confirmDeleteBtn')?.addEventListener('click', function () {
        const btn = this;
        const id = document.getElementById('deleteId').value;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

        fetch(`<?= BASE_URL ?>/api/modul/delete`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        })
        .then(r => r.json())
        .then(data => {
            if (data.status === 'success') {
                toastr.success(data.message || 'Kayıt silindi.');
                bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();
                setTimeout(() => location.reload(), 800);
            } else {
                toastr.error(data.message || 'Silme başarısız.');
                btn.disabled = false;
                btn.innerHTML = '<i class="ti ti-trash me-1"></i> Evet, Sil';
            }
        })
        .catch(() => {
            toastr.error('Sunucu hatası.');
            btn.disabled = false;
            btn.innerHTML = '<i class="ti ti-trash me-1"></i> Evet, Sil';
        });
    });
})();
</script>
```

### Create / Edit Form Şablonu

```php
<?php defined('BASEPATH') or die(); ?>

<div class="page-header d-print-none">
  <div class="container-xl">
    <div class="row g-2 align-items-center">
      <div class="col">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>">Ana Sayfa</a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/modul">Modül</a></li>
            <li class="breadcrumb-item active"><?= isset($record) ? 'Düzenle' : 'Yeni Ekle' ?></li>
          </ol>
        </nav>
        <h2 class="page-title"><?= isset($record) ? 'Kaydı Düzenle' : 'Yeni Kayıt' ?></h2>
      </div>
    </div>
  </div>
</div>

<div class="page-body">
  <div class="container-xl">
    <div class="row">
      <div class="col-12 col-lg-8">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title"><i class="ti ti-form me-2 text-primary"></i> Temel Bilgiler</h3>
          </div>
          <div class="card-body">
            <div id="formErrors" class="alert alert-danger d-none">
              <i class="ti ti-alert-triangle me-2"></i><span id="formErrorText"></span>
            </div>
            <div class="row g-3">
              <div class="col-12">
                <label class="form-label required">Ad</label>
                <input type="text" id="name" class="form-control"
                       value="<?= htmlspecialchars($record['name'] ?? '') ?>">
                <div class="invalid-feedback" id="nameError"></div>
              </div>
              <div class="col-12 col-md-6">
                <label class="form-label">Durum</label>
                <div class="form-check form-switch mt-1">
                  <input class="form-check-input" type="checkbox" id="status"
                         <?= ($record['status'] ?? 1) == 1 ? 'checked' : '' ?>>
                  <label class="form-check-label" for="status">Aktif</label>
                </div>
              </div>
              <div class="col-12">
                <label class="form-label">Açıklama</label>
                <textarea id="description" class="form-control" rows="4"><?= htmlspecialchars($record['description'] ?? '') ?></textarea>
              </div>
            </div>
          </div>
          <div class="card-footer">
            <div class="row">
              <div class="col">
                <a href="<?= BASE_URL ?>/modul" class="btn btn-ghost-secondary">
                  <i class="ti ti-arrow-left me-1"></i> Geri
                </a>
              </div>
              <div class="col-auto">
                <button id="saveBtn" type="button" class="btn btn-primary">
                  <i class="ti ti-device-floppy me-1"></i> Kaydet
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
(function () {
    'use strict';

    const recordId = <?= isset($record) ? $record['id'] : 'null' ?>;

    document.getElementById('saveBtn').addEventListener('click', function () {
        const btn = this;
        document.getElementById('formErrors').classList.add('d-none');
        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

        const payload = {
            id: recordId,
            name: document.getElementById('name').value.trim(),
            status: document.getElementById('status').checked ? 1 : 0,
            description: document.getElementById('description').value.trim()
        };

        if (!payload.name) {
            document.getElementById('name').classList.add('is-invalid');
            document.getElementById('nameError').textContent = 'Ad zorunludur.';
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Kaydediliyor...';

        const url = recordId
            ? '<?= BASE_URL ?>/api/modul/update'
            : '<?= BASE_URL ?>/api/modul/store';

        fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(r => r.json())
        .then(data => {
            if (data.status === 'success') {
                toastr.success(data.message || 'Kaydedildi.');
                setTimeout(() => window.location.href = '<?= BASE_URL ?>/modul', 800);
            } else {
                if (data.errors) {
                    Object.keys(data.errors).forEach(field => {
                        document.getElementById(field)?.classList.add('is-invalid');
                        const errEl = document.getElementById(field + 'Error');
                        if (errEl) errEl.textContent = data.errors[field];
                    });
                } else {
                    document.getElementById('formErrors').classList.remove('d-none');
                    document.getElementById('formErrorText').textContent = data.message || 'Hata oluştu.';
                }
                btn.disabled = false;
                btn.innerHTML = '<i class="ti ti-device-floppy me-1"></i> Kaydet';
            }
        })
        .catch(() => {
            toastr.error('Sunucu hatası.');
            btn.disabled = false;
            btn.innerHTML = '<i class="ti ti-device-floppy me-1"></i> Kaydet';
        });
    });
})();
</script>
```

---

## Sık Kullanılan Bileşenler

### Kart
```html
<div class="card">
  <div class="card-header">
    <h3 class="card-title">Başlık</h3>
  </div>
  <div class="card-body">İçerik</div>
  <div class="card-footer">
    <button class="btn btn-primary">Kaydet</button>
  </div>
</div>
```

### Badge (Durum)
```html
<span class="badge bg-success-lt text-success">Aktif</span>
<span class="badge bg-danger-lt text-danger">Pasif</span>
<span class="badge bg-warning-lt text-warning">Beklemede</span>
```

### Boş Durum
```html
<div class="empty">
  <div class="empty-icon"><i class="ti ti-inbox" style="font-size:3rem;color:var(--tblr-muted)"></i></div>
  <p class="empty-title">Kayıt bulunamadı</p>
  <p class="empty-subtitle text-secondary">Henüz hiç kayıt eklenmemiş.</p>
</div>
```

### Modal (Onay)
```html
<div class="modal modal-blur fade" id="confirmModal" tabindex="-1">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-body">
        <div class="modal-title">Emin misiniz?</div>
        <div>Bu işlem geri alınamaz.</div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">İptal</button>
        <button class="btn btn-danger" id="confirmBtn">Onayla</button>
      </div>
    </div>
  </div>
</div>
```

### İstatistik Kartı
```html
<div class="card card-sm">
  <div class="card-body">
    <div class="row align-items-center">
      <div class="col-auto">
        <span class="bg-primary text-white avatar"><i class="ti ti-users"></i></span>
      </div>
      <div class="col">
        <div class="font-weight-medium">Toplam Kullanıcı</div>
        <div class="text-secondary h3 mb-0"><?= $stats['users'] ?></div>
      </div>
    </div>
  </div>
</div>
```

---

## Üretim Kontrol Listesi

Kod yazarken şunları kontrol et:

- [ ] `defined('BASEPATH') or die()` var mı?
- [ ] PHP değişkenleri `htmlspecialchars()` ile escape ediliyor mu?
- [ ] Boş durum (empty state) kontrolü var mı?
- [ ] AJAX butonlarında spinner var mı?
- [ ] `alert()` yerine `toastr` kullanılıyor mu?
- [ ] İkonlar `ti ti-*` formatında mı?
- [ ] Tablolar `table-responsive` içinde mi?
- [ ] Renkler CSS variable ile mi? (`var(--tblr-*)`)
