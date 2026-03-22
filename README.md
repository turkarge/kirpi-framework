# kirpi-framework

Lightweight, modular PHP 8.4 framework.

## Local Preview

- Runtime page: `http://localhost/kirpi`
- Health endpoint: `http://localhost/health`
- Monitor (when enabled): `http://localhost/kirpi-monitor`
- AI SQL test (when enabled): `http://localhost/kirpi/ai-sql-test`

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
