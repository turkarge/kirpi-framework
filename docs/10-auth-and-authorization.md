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

Sifre sifirlama akisinda gercek token tablosu (`password_reset_tokens`) kullanilir ve sifre basariyla degistiginde kullanicinin lock PIN'i temizlenir.

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
- Sistem log ekrani (`/logs`) `logs.view` izni ile korunur.

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
- `database/migrations/2026_04_07_000006_create_password_reset_tokens_table.php`

## 5) Varsayilan Isletim Notlari

- Kurulumdan sonra `php framework migrate` ile tablo yapisi guncel tutulur.
- Kurulumdan sonra `php framework setup:roles` varsayilan rollerle birlikte varsayilan izinleri de yazar.
- Kurulumdan sonra `php framework setup:check` ile tablo/rol/permission ve HTTP smoke kontrolu calistirilir.
- Yetki matrisi UI uzerinden yonetilir ve route enforcement middleware ile aktif olarak uygulanir.
- Uygulama ekipleri kendi modul izin anahtarlarini `permissionCatalog()` yapisina ekleyebilir.

## 6) Route Enforcement

Cekirdek route korumasi middleware ile yapilir:

- `auth`: kimlik dogrulama zorunlu
- `permission:<key>`: kullanicinin ilgili izne sahip olmasi zorunlu

Ornek:

- `dashboard` -> `permission:dashboard.view`
- `roles/matrix` -> `permission:roles.matrix`
- `users` yazma islemleri -> `permission:users.create|users.update|users.toggle`

Web isteklerinde yetki yoksa kullanici dashboard'a yonlendirilir ve uyari flash mesaji gosterilir.
API isteklerinde ise `403` JSON donulur.
