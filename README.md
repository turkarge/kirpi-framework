# kirpi-framework

Lightweight, modular PHP 8.4 framework.

## Dokumantasyon

- Ana dokuman index: `docs/README.md`
- GitHub dokumanlari: `https://github.com/turkarge/kirpi-framework/tree/main/docs`
- Son kontrol seti: `RELEASE_READINESS_CHECKLIST.md`

## AI Skill Sistemi

- Canonical contract: `ai-skills/SKILL_CONTRACT.md`
- Skill manifest: `ai-skills/manifest.json`
- Moduler skilller: `ai-skills/skills/*`
- Editor adapterlari:
  - `CLAUDE.md`
  - `.github/copilot-instructions.md`
  - `ai-skills/adapters/*`

## Local Preview

- App landing page: `http://localhost/`
- Health endpoint: `http://localhost/health`
- Core login page: `http://localhost/login`
- Core dashboard (auth required): `http://localhost/dashboard`
- Role management (auth required): `http://localhost/roles`
- Permission matrix (auth required): `http://localhost/roles/matrix`
- Manager API (manager context): `http://localhost:8081/manager/api/overview?token=...`

## Nginx Note

The default vhost proxies only the main `app` service. If you need a separate `manager` vhost, keep it in a separate nginx config or compose override so missing upstreams do not crash the default nginx container.

## Frontend Standardlar

- Frontend feature test-page standardi: `docs/FRONTEND_TEST_PAGE_STANDARD.md`
- Ilk uygulama rehberi: `docs/FIRST_APP_GUIDE.md`
- Ayar stratejisi: `docs/SETTINGS_STRATEGY.md`

## Feature Flags

- `KIRPI_FEATURE_COMMUNICATION=true|false`
- `KIRPI_FEATURE_AI=true|false` (default: false)
- `KIRPI_AUTH_LOGIN_COVER=` (opsiyonel, login sag cover gorseli; bos ise Kirpi varsayilan cover'i kullanilir)

## Logging Standardi

- Loglar kanal bazli gunluk dosyalara yazilir: `storage/logs/YYYY-MM-DD-<channel>.log`
- Varsayilan format: `json` (opsiyonel: `line`)
- Request lifecycle logu varsayilan acik:
  - `channel=request`
  - alanlar: `request_id`, `method`, `path`, `status`, `duration_ms`, `ip`, `user_id`, `user_agent`
- Auth guvenlik loglari:
  - `channel=auth` (login/logout/unlock/reset success)
  - `channel=security` (login/unlock fail)
  - `channel=audit` (kritik degisiklikler)
- Hassas veriler otomatik maskelenir (`password`, `pin`, `token`, `secret`, `authorization`, vb.)
- Log ayarlari:
  - `LOG_LEVEL=DEBUG|INFO|WARNING|ERROR`
  - `LOG_FORMAT=json|line`
  - `LOG_REQUESTS=true|false`

## Backup Settings

- `KIRPI_BACKUP_DIR` (default: `storage/backups`)
- `KIRPI_BACKUP_RETENTION` (default: `10`)
- `KIRPI_BACKUP_USE_DOCKER=true|false` (default: `true`, failure durumunda native dump fallback devreye girer)
- `KIRPI_BACKUP_MYSQL_CONTAINER` (default: bos; sadece `docker exec` kullanmak istenirse container adi verilir)

## Manager Context

- `APP_CONTEXT=manager` oldugunda sadece `routes/manager.php` yuklenir.
- Manager API endpointleri `KIRPI_MANAGER_TOKEN` ile korunur (`X-Manager-Token` veya `?token=`).
- Opsiyonel IP kisiti: `KIRPI_MANAGER_IP_WHITELIST=127.0.0.1,172.18.0.1`
- Docker compose'ta manager gateway: `http://localhost:8081`
- Manager paneli minimal tutulur:
  - overview
  - health
  - ready

## Auth ve Yetkilendirme

- Core auth akisi:
  - `GET /login`, `POST /login`
  - `GET|POST /exit` (logout)
  - `GET /dashboard` (auth gerekli)
- Roller:
  - Listeleme / olusturma / duzenleme ekranlari `modules/Roles` altindadir.
  - Ilk kurulumda varsayilan roller migration/seed akisiyla olusur.
- Yetki matrisi:
  - `GET /roles/matrix`: moduller bazinda accordion permission matrix
  - `POST /roles/matrix`: secilen izinleri kaydeder
  - Kayit modeli: `role_permissions(role_id, permission_key, is_allowed)`
  - Not: Pasif roller matrix ekraninda sadece okunur gorunur.

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
- Setup wizard (interactive): `php framework setup`
- Setup wizard wrappers:
  - Windows PowerShell: `./setup.ps1`
  - Windows CMD: `setup.bat`
  - Linux/macOS: `./setup.sh`
- Setup profile override:
  - `php framework setup --profile=local`
  - `php framework setup --profile=cloud`
- Create/update initial admin manually:
  - `php framework setup:admin --name="Kirpi Admin" --email="admin@example.com" --password="secret"`

## Kurulum Komutlari (OS Bazli)

- Windows PowerShell
  - Interactive local: `./setup.ps1 -Profile local` (veya `./setup.ps1 --profile local`)
  - Interactive cloud: `./setup.ps1 -Profile cloud` (veya `./setup.ps1 --profile cloud`)
  - Non-interactive: `./setup.ps1 -Profile local -NonInteractive`
- Windows CMD
  - Interactive local: `setup.bat --profile local`
  - Interactive cloud: `setup.bat --profile cloud`
  - Non-interactive: `setup.bat --profile local --non-interactive`
- Linux/macOS
  - Interactive local: `./setup.sh --profile local`
  - Interactive cloud: `./setup.sh --profile cloud`
  - Non-interactive: `./setup.sh --profile local --non-interactive`

Not:
- Linux/macOS tarafinda ilk kullanimda executable izni verin: `chmod +x setup.sh`
- `setup` preflight kontrolu PHP/Composer/Docker durumunu denetler; local profilde Docker daemon kapaliysa kurulum durur ve net yonlendirme verir.
- `DB mode = internal` secildiginde DB host/database/user/password degerleri setup tarafindan otomatik atanir.
- Local kurulumda `docker compose up` sonrasinda setup geri sayim yapar, ardindan migrate ve ilk admin adimlarini otomatik (retry ile) tamamlar.
- `setup` standart akista Tabler dosyalarini GitHub'dan otomatik senkronize eder (`KIRPI_TABLER_REF`, varsayilan: `main`, fallback: `main`).
- `setup` sirasinda `preview/pages` dosyalari runtime'a alinmaz; `ai-skills/references/tabler/pages` altina UX referansi olarak kaydedilir.

## Tabler Standardi

- Standart kaynak: `https://github.com/tabler/tabler`
- Standart ref: `main`
- Override gerekiyorsa sadece `KIRPI_TABLER_REF` ile acikca verilir (ornek: `main` veya belirli bir tag/branch).
- Ref gecersizse setup `main` ile devam etmeyi dener.
- Runtime varlik standardi: sadece `public/vendor/tabler/dist`.
- Tabler shell dosyasi cekirdek tarafinda tutulur: `core/Frontend/templates/admin/layout-shell.html`.
