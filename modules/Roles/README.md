# Roles Module

## Amac

Kirpi Framework icinde rol kayitlarini ve rol bazli izinleri yonetir.

## Bilesenler

- Controller: `Controllers/RoleManagementController.php`
- Modeller:
  - `Models/Role.php`
  - `Models/RolePermission.php`
- Route dosyasi: `routes/web.php`

## Ekranlar

- `/roles`: rol listesi + olusturma
- `/roles/{role}`: rol detay
- `/roles/{role}/edit`: rol duzenleme
- `/roles/matrix`: yetki matrisi

## Yetki Matrisi

- Moduller accordion olarak gorunur.
- Satirlar permission key, sutunlar rol bazlidir.
- Kaydetme `POST /roles/matrix` ile yapilir.
- Veriler `role_permissions` tablosuna yazilir.
