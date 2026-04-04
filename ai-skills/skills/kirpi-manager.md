# Skill: Kirpi Manager

## Kapsam

- manager panel ekranlari
- manager API endpointleri
- backup center
- manager guvenlik hardening

## Pratik Kurallar

- tum manager API endpointleri `manager.token` middleware altinda olmali
- kritik operasyonlarda audit log dusulmeli
- backup dosyalari repoya girmez (`storage/backups/*` ignore)

## Referanslar

- `manager/src/Http/Controllers/ControlPlaneController.php`
- `manager/src/Http/Middleware/RequireManagerToken.php`
- `manager/src/Backup/BackupService.php`
- `routes/manager.php`

## Operasyon Kurali

- backup olusturma tek basina yeterli degil; restore tatbikati checklistte ayrica takip edilir.
