# kirpi-framework

Lightweight, modular PHP 8.4 framework.

## Dokumantasyon

- Ana dokuman index: `docs/README.md`
- GitHub dokumanlari: `https://github.com/turkarge/kirpi-framework/tree/main/docs`
- Son kontrol seti: `RELEASE_READINESS_CHECKLIST.md`

## Local Preview

- App landing page: `http://localhost/`
- Health endpoint: `http://localhost/health`
- Manager Control Plane (manager context): `http://localhost:8081/manager?token=...`
- Manager Core: `http://localhost:8081/manager/core?token=...`
- Manager Modules: `http://localhost:8081/manager/modules?token=...`
- Manager Integrations: `http://localhost:8081/manager/integrations?token=...`
- Manager Developer: `http://localhost:8081/manager/developer?token=...`
- Manager System: `http://localhost:8081/manager/system?token=...`
- Manager Backup Center: `http://localhost:8081/manager/backup?token=...`

## Nginx Note

The default vhost proxies only the main `app` service. If you need a separate `manager` vhost, keep it in a separate nginx config or compose override so missing upstreams do not crash the default nginx container.

## Frontend Standardlar

- Frontend feature test-page standardi: `docs/FRONTEND_TEST_PAGE_STANDARD.md`
- Ilk uygulama rehberi: `docs/FIRST_APP_GUIDE.md`
- Ayar stratejisi: `docs/SETTINGS_STRATEGY.md`

## Feature Flags

- `KIRPI_FEATURE_MONITORING=true|false`
- `KIRPI_FEATURE_COMMUNICATION=true|false`
- `KIRPI_FEATURE_AI=true|false` (default: false)

## Backup Settings

- `KIRPI_BACKUP_DIR` (default: `storage/backups`)
- `KIRPI_BACKUP_RETENTION` (default: `10`)
- `KIRPI_BACKUP_USE_DOCKER=true|false` (default: `true`, failure durumunda native dump fallback devreye girer)
- `KIRPI_BACKUP_MYSQL_CONTAINER` (default: `kirpi_mysql`)

## Manager Context

- `APP_CONTEXT=manager` oldugunda sadece `routes/manager.php` yuklenir.
- Manager API endpointleri `KIRPI_MANAGER_TOKEN` ile korunur (`X-Manager-Token` veya `?token=`).
- Opsiyonel IP kisiti: `KIRPI_MANAGER_IP_WHITELIST=127.0.0.1,172.18.0.1`
- Docker compose'ta manager gateway: `http://localhost:8081`
- Manager paneli (`/manager`) icinde:
  - runtime API kontrolu (`ready/self-check/history`)
  - module wizard (`make:module` + `make:crud`)
  - mail test
  - backup center (full/db backup, checksum verify, download/delete)
  - dev lab linkleri (UI Kit, Notify, PWA, Modal, Import/Export, State, A11y, Monitor, Runtime)

## AI (External Providers)

- AI SQL stack provider-agnostic calisir; varsayilan `null` provider'dir.
- Desteklenen provider'lar:
  - `openai`
  - `anthropic`
- Ornek env (OpenAI):
  - `AI_PROVIDER=openai`
  - `AI_MODEL=gpt-4.1-mini`
  - `AI_OPENAI_API_KEY=...`
  - `AI_OPENAI_BASE_URL=https://api.openai.com/v1`
- Ornek env (Anthropic):
  - `AI_PROVIDER=anthropic`
  - `AI_MODEL=claude-3-5-haiku-latest`
  - `AI_ANTHROPIC_API_KEY=...`
  - `AI_ANTHROPIC_BASE_URL=https://api.anthropic.com/v1`
- Ortak ayarlar:
  - `AI_TIMEOUT=60`
  - `AI_TRACE_ENABLED=true|false`
  - `AI_SQL_MAX_CELL_LENGTH=280`
- Trace log file (when enabled):
  - `storage/logs/YYYY-MM-DD-ai-trace.log`

## CLI Generators

- Module skeleton: `php framework make:module Catalog`
- CRUD scaffold: `php framework make:crud Catalog Product`
