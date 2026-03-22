# 04 - Configuration

Konfig kontrolu ana olarak `.env` uzerindedir.

## Critical Keys

- `APP_ENV`, `APP_DEBUG`, `APP_URL`
- `APP_CONTEXT` (`app` veya `manager`)
- `KIRPI_MANAGER_TOKEN`

## Feature Flags

- `KIRPI_FEATURE_MONITORING`
- `KIRPI_FEATURE_COMMUNICATION`
- `KIRPI_FEATURE_AI`

## Database

- `DB_CONNECTION`
- `DB_HOST`, `DB_PORT`
- `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`

## Backup

- `KIRPI_BACKUP_DIR` (default `storage/backups`)
- `KIRPI_BACKUP_RETENTION` (default `10`)
- `KIRPI_BACKUP_USE_DOCKER`
- `KIRPI_BACKUP_MYSQL_CONTAINER`

## Security Notes

- `.env` asla repoya girmez
- manager token uzun ve rastgele olmali
- production'da `APP_DEBUG=false`
