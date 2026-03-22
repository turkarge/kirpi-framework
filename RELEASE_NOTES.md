# Kirpi Framework - Release Notes

## [2026-03-22] [Core] v1.0.0 Stabil Cekirdek Yayini

### Neden?
- Cekirdek mimari, manager paneli, backup merkezi, dokumantasyon ve landing sadelestirme adimlari tamamlandi.
- Framework'u stabil bir milestone ile etiketlemek gerekiyordu.

### Ne Degisti?
- App tarafi sade bir landing (`/`) + temel health/readiness endpointleri ile sinirlandi.
- Manager tarafi cok sayfali kontrol paneline tasindi: `Core / Modules / Integrations / Developer / System`.
- Manager Backup Center eklendi: backup olusturma, listeleme, verify, indirme, silme.
- Manager guvenlik katmani sertlestirildi: token zorunlulugu + opsiyonel IP allowlist + audit log + throttle.
- Dokumantasyon seti Turkce ve kapsamli sekilde tamamlandi (`docs/*`).

### Etkilenen Yuzey
- Route/Modul:
  - App: `/`, `/health`, `/ready`
  - Manager: `/manager/*`, `/manager/api/*`
- Dosyalar:
  - [routes/web.php](/d:/projeler/kirpi-framework/routes/web.php)
  - [routes/manager.php](/d:/projeler/kirpi-framework/routes/manager.php)
  - [manager/src/Http/Middleware/RequireManagerToken.php](/d:/projeler/kirpi-framework/manager/src/Http/Middleware/RequireManagerToken.php)
  - [manager/src/Backup/BackupService.php](/d:/projeler/kirpi-framework/manager/src/Backup/BackupService.php)
  - [RELEASE_READINESS_CHECKLIST.md](/d:/projeler/kirpi-framework/RELEASE_READINESS_CHECKLIST.md)
  - [docs/README.md](/d:/projeler/kirpi-framework/docs/README.md)
  - [README.md](/d:/projeler/kirpi-framework/README.md)

### Geriye Donuk Etki
- Breaking: Evet (app tarafindaki eski `/kirpi/*` demo/test route'lari kaldirildi; manager tarafina tasindi).
- Ek adim:
  - `KIRPI_MANAGER_TOKEN` zorunlu ayarlanmali.
  - Opsiyonel: `KIRPI_MANAGER_IP_WHITELIST` ile manager IP kisiti acilabilir.

### Dogrulama
- `vendor/bin/phpunit --testsuite Unit` -> Sonuc: `OK (77 tests, 193 assertions)`
- `GET http://localhost/` -> Sonuc: `200`
- `GET http://localhost:8081/manager/api/overview?token=...` -> Sonuc: `200`
- Backup smoke:
  - `create?mode=db` -> `ok: true`
  - `create?mode=full` -> `ok: true`
  - `verify` -> `valid: true`

### Commit/Push
- SHA: `c3efde5`
- Mesaj: `chore(release): add readiness checklist and harden manager access controls`

---

# Kirpi Framework - Release Note Standardi

Bu dosya, her push/iterasyon sonunda paylasilacak release note formatini standartlastirir.

## 1) Baslik
Format:
`[Tarih] [Alan] Kisa Degisiklik Ozeti`

Ornek:
`2026-03-21 Frontend: Tabler shell patch katmani stabilize edildi`

## 2) Zorunlu Bolumler
Her release note su 6 bolumu icermelidir:

1. `Neden?`
- Bu degisiklik hangi problemi cozuyor?

2. `Ne Degisti?`
- Teknik olarak hangi dosya/sinif/davranis degisti?

3. `Etkilenen Yuzey`
- Hangi route/komut/modul etkilendi?

4. `Geriye Donuk Etki`
- Breaking change var mi?
- Migration/env/config degisikligi gerekiyor mu?

5. `Dogrulama`
- Hangi test/komut calisti?
- Sonuc nedir?

6. `Commit/Push`
- Commit SHA
- Commit mesaji

## 3) Yazim Kurallari
- Pazarlama dili kullanma; teknik ve dogrulanabilir yaz.
- "Iyilestirildi", "duzeltildi" gibi genel ifadeler yerine somut davranis degisikligi yaz.
- Dosya referanslarini mutlak path ile ver.
- Test bolumu bos gecilemez. Test calismadiysa sebebi acik yazilir.

## 4) Kapsam Etiketleri
Notun basliginda su etiketlerden biri kullanilir:
- `Core`
- `Routing`
- `Database`
- `Auth`
- `Frontend`
- `Runtime`
- `Tests`
- `Docs`

## 5) Kisa Release Note Sablonu
```md
## [Tarih] [Etiket] Baslik

### Neden?
- ...

### Ne Degisti?
- ...

### Etkilenen Yuzey
- Route/Modul: ...
- Dosyalar:
  - ...

### Geriye Donuk Etki
- Breaking: Yok/Var (...)
- Ek adim: Yok/Var (...)

### Dogrulama
- `...komut...` -> Sonuc: ...

### Commit/Push
- SHA: `...`
- Mesaj: `...`
```

## 6) Uygulama Karari
- Bu standart, Kirpi Framework'de **varsayilan release note formati** olarak kullanilir.
- TODO guncellemesi yapilan her iterasyonda bu formatla not cikilir.
