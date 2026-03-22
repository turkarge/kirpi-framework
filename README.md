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

## Feature Flags

- `KIRPI_FEATURE_MONITORING=true|false`
- `KIRPI_FEATURE_COMMUNICATION=true|false`
- `KIRPI_FEATURE_AI=true|false` (default: false)

## AI (Ollama)

- Optional service (docker profile): `docker compose --profile ai up -d ollama`
- Pull model: `docker compose exec -T ollama ollama pull qwen2.5-coder:3b`
- Required env:
  - `AI_PROVIDER=ollama`
  - `AI_MODEL=qwen2.5-coder:3b`
  - `AI_OLLAMA_BASE_URL=http://ollama:11434`
  - `AI_TRACE_ENABLED=true|false`
  - `AI_SQL_MAX_CELL_LENGTH=280`
- Trace log file (when enabled):
  - `storage/logs/YYYY-MM-DD-ai-trace.log`
