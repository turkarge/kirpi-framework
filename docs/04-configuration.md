# 04 - Konfigurasyon

Konfigurasyonun ana kaynagi `.env` dosyasidir.

## Kritik Anahtarlar

- `APP_ENV`, `APP_DEBUG`, `APP_URL`
- `APP_CONTEXT` (`app` veya `manager`)
- `KIRPI_MANAGER_TOKEN`

## Ozellik Bayraklari

- `KIRPI_FEATURE_MONITORING`
- `KIRPI_FEATURE_COMMUNICATION`
- `KIRPI_FEATURE_AI`

## Veritabani

- `DB_CONNECTION`
- `DB_HOST`, `DB_PORT`
- `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`

## Backup

- `KIRPI_BACKUP_DIR` (varsayilan `storage/backups`)
- `KIRPI_BACKUP_RETENTION` (varsayilan `10`)
- `KIRPI_BACKUP_USE_DOCKER`
- `KIRPI_BACKUP_MYSQL_CONTAINER`

## Guvenlik Notlari

- `.env` repoya girmez
- manager token uzun ve rastgele olmali
- production ortaminda `APP_DEBUG=false` olmalidir
