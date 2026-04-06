# 06 - Manager API

Manager artik HTML panel degil, token korumali API yuzeyi olarak calisir.

## Base URL

- `http://localhost:8081/manager/api`

## Kimlik Dogrulama

- Header: `X-Manager-Token: <KIRPI_MANAGER_TOKEN>`
- veya query: `?token=<KIRPI_MANAGER_TOKEN>`

## Endpointler

- `GET /overview`
- `GET /health`
- `GET /ready`

Ornek:

```bash
curl "http://localhost:8081/manager/api/overview?token=<KIRPI_MANAGER_TOKEN>"
```

## Guvenlik Notu

- Token `.env` icinde tutulur, repoya yazilmaz.
- Opsiyonel IP kisiti: `KIRPI_MANAGER_IP_WHITELIST`
