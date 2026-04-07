# 10 - Auth ve Yetkilendirme

Bu dokuman, Kirpi Framework icindeki cekirdek login/logout akisi ile rol/yetki yapisini aciklar.

## 1) Kimlik Dogrulama (Auth)

Temel web auth endpointleri:

- `GET /login`: login sayfasini gosterir
- `POST /login`: kullaniciyi dogrular ve session acilir
- `GET /exit` ve `POST /exit`: session kapatir (logout)
- `GET /dashboard`: sadece giris yapmis kullaniciya aciktir

Ek sayfalar:

- `GET /forgot-password`
- `GET /tos`
- `GET /lock`

## 2) Rol Yonetimi

Rol modulu:

- Kod konumu: `modules/Roles`
- Web route dosyasi: `modules/Roles/routes/web.php`
- Controller: `Modules\Roles\Controllers\RoleManagementController`

Temel role endpointleri:

- `GET /roles`
- `POST /roles`
- `GET /roles/{role}`
- `GET /roles/{role}/edit`
- `PUT /roles/{role}/status`

## 3) Yetki Matrisi

Role bazli izin yonetimi tek ekranda matrix mantigi ile yapilir.

Endpointler:

- `GET /roles/matrix`
- `POST /roles/matrix`

Ekran davranisi:

- Her modul (Dashboard, Users, Roles, Locales) ayri accordion panelinde listelenir.
- Satirlar izin anahtarlarini (`permission_key`) temsil eder.
- Sutunlar rollerden olusur.
- Checkbox secimleri kaydedildiginde role ait izin satirlari yeniden yazilir.
- Pasif roller matrixte gorunur, fakat checkbox alanlari devre disi tutulur.

## 4) Veri Modeli

`role_permissions` tablosu:

- `id`
- `role_id`
- `permission_key`
- `is_allowed`
- `created_at`
- `updated_at`

Kurallar:

- `role_id + permission_key` benzersizdir (unique).
- Indexler: `role_id`, `permission_key`.

Model:

- `Modules\Roles\Models\RolePermission`

Migration:

- `database/migrations/2026_04_07_000004_create_role_permissions_table.php`

## 5) Varsayilan Isletim Notlari

- Kurulumdan sonra `php framework migrate` ile tablo yapisi guncel tutulur.
- Yetki matrisi bugun UI seviyesinde yonetilir; route enforcement middleware/policy adimi ayrica baglanabilir.
- Uygulama ekipleri kendi modul izin anahtarlarini `permissionCatalog()` yapisina ekleyebilir.
