# kirpi-framework

Lightweight, modular PHP 8.4 framework.

## Local Preview

- Runtime page: `http://localhost/kirpi`
- Health endpoint: `http://localhost/health`
- Monitor (when enabled): `http://localhost/kirpi-monitor`

## Nginx Note

The default vhost proxies only the main `app` service. If you need a separate `manager` vhost, keep it in a separate nginx config or compose override so missing upstreams do not crash the default nginx container.

## Frontend Standardlar

- Frontend feature test-page standardi: `docs/FRONTEND_TEST_PAGE_STANDARD.md`
