# 03 - Installation

## 1) Clone

```bash
git clone https://github.com/turkarge/kirpi-framework.git
cd kirpi-framework
```

## 2) Environment

```bash
cp .env.example .env
```

Ayarla:

- `APP_KEY`
- `DB_*`
- `KIRPI_MANAGER_TOKEN`

## 3) Start Services

```bash
docker compose up -d --build
```

## 4) Verify

- App: `http://localhost`
- Health: `http://localhost/health`
- Manager: `http://localhost:8081/manager?token=<KIRPI_MANAGER_TOKEN>`

## 5) Tests

```bash
vendor/bin/phpunit --testsuite Unit
```
