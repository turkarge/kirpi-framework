# Release Readiness Checklist

Tarih: 2026-04-11

Bu dosya, Kirpi Framework icin "lokal tamam / cloud smoke oncesi" durumunu tek yerde toplar.

## 1) Lokal Kurulum ve Cekirdek Akis

- [x] `php framework setup --profile=local` akisi tamam
- [x] Setup sonunda `migrate + setup:roles + setup:admin + setup:check` zinciri calisiyor
- [x] `/health` ve `/ready` lokalde healthy
- [x] Login -> Dashboard -> Logout temel auth akis testi gecti

## 2) Yetki ve Yonetim Modulleri

- [x] Varsayilan roller (`super-admin`, `admin`, `editor`, `viewer`) olusuyor
- [x] Varsayilan permission senkronu dogrulanmis durumda
- [x] Roller / Kullanicilar / Dil Yonetimi sayfalari calisiyor
- [x] Log goruntuleme sayfasi (`/logs`) ve `logs.view` izni aktif

## 3) Test ve Kod Guvencesi

- [x] Hedef unit testler yesil (`UserPermissionTest`, `SetupRolesCommandTest`)
- [x] `setup:check` komutu DB/tablo/rol/permission/HTTP smoke kontrollerini yapiyor
- [x] Setup role seed mismatch durumunda komut fail ediyor (sessiz gecmiyor)

## 4) UI ve Dokumantasyon

- [x] Tabler tabanli auth ve dashboard shell stabil
- [x] Login/forgot/tos/lock sayfalari ortak dil ve marka ayarlariyla calisiyor
- [x] CLI ve setup dokumantasyonu guncel
- [x] Auth + authorization dokumani guncel (password reset + lock pin davranisi dahil)

## 5) Cloud / Dokploy Hazirlik

- [x] Cloud profile setup raporu ve `dokploy-next-steps.md` olusuyor
- [x] `docker-compose.yml` host port bind yerine Dokploy uyumlu `expose` kullaniyor
- [ ] Dokploy deploy sonrasi `php framework setup:check --url=<domain>` tamamen yesil
- [ ] `/ready` cloud fail kok nedeni netlestirilip kalici cozum dokumante edildi
- [ ] Build/deploy asamasinda `composer install` otomasyonu netlestirildi

## 6) Release Kapama

- [ ] `v1.0.0` (veya hedef surum) tag acildi
- [ ] `RELEASE_NOTES.md` son degisikliklerle guncellendi
- [ ] Son smoke seti (local + cloud) tek raporda arsivlendi
