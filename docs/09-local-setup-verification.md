# 09 - Local Setup Dogrulama

Bu kontrol listesi yeni bir makinede Kirpi local kurulumunun saglikli oldugunu hizli dogrulamak icindir.

## 1) Kurulum

```bash
php framework setup --profile=local
```

Beklenen:

- preflight `ok`
- docker containerlari ayakta
- migrate tamam
- `setup:roles`, `setup:admin` ve `setup:check` otomatik calismis
- setup raporu yazildi (`storage/setup/*.json`)

## 2) HTTP Saglik Kontrolleri

- `GET /health` => `status: healthy`
- `GET /ready` => `status: healthy`

## 3) Manager API Kontrolu

- `GET /manager/api/overview?token=...` => `ok: true`
- `GET /manager/api/health?token=...` => `ok: true`
- `GET /manager/api/ready?token=...` => `ok: true`

## 4) Auth Akisi

- `GET /login` acilmali
- setup sirasinda olusan admin ile login
- `GET /dashboard` 200 donmeli

## 5) Hizli Teknik Kontrol

```bash
docker compose exec -T app php framework setup:check --url=http://nginx
vendor/bin/phpunit tests/Unit/LayoutTransformerTest.php
```

Beklenen:

- `setup:check` tum maddelerde `ok`
- testler yesil.
