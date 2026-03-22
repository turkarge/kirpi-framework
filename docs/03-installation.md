# 03 - Kurulum

## 1) Depoyu Klonla

```bash
git clone https://github.com/turkarge/kirpi-framework.git
cd kirpi-framework
```

## 2) Ortam Dosyasi

```bash
cp .env.example .env
```

Ayarla:

- `APP_KEY`
- `DB_*`
- `KIRPI_MANAGER_TOKEN`

## 3) Servisleri Baslat

```bash
docker compose up -d --build
```

## 4) Dogrula

- App: `http://localhost`
- Health: `http://localhost/health`
- Manager: `http://localhost:8081/manager?token=<KIRPI_MANAGER_TOKEN>`

## 5) Testleri Calistir

```bash
vendor/bin/phpunit --testsuite Unit
```
