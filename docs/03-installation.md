# 03 - Kurulum

## 1) Depoyu Klonla

```bash
git clone https://github.com/turkarge/kirpi-framework.git
cd kirpi-framework
```

## 2) Kurulum Wizard'i (onerilen)

```bash
php framework setup --profile=local
```

Wizard asagidaki adimlari otomatik yapar:

- preflight kontrolu (PHP, Composer, Docker, daemon)
- `.env` olusturma/guncelleme
- Tabler `dist` + UX referanslari sync
- `docker compose up -d --build`
- migrate + ilk admin olusturma
- health/ready kontrolu

## 3) Dogrula

- App: `http://localhost`
- Health: `http://localhost/health`
- Ready: `http://localhost/ready`
- Manager API: `http://localhost:8081/manager/api/overview?token=<KIRPI_MANAGER_TOKEN>`

## 4) Testleri Calistir

```bash
vendor/bin/phpunit --testsuite Unit
```
