# 08 - CLI Referansi

## Cekirdek Komutlar

```bash
php framework make:module Catalog
php framework make:crud Catalog Product
php framework migrate
php framework migrate:rollback
php framework cache:clear
php framework setup --profile=local
php framework setup --profile=cloud
php framework setup:roles
php framework setup:admin --name="Kirpi Admin" --email="admin@example.com" --password="secret"
php framework setup:check --url=http://localhost
```

## Test

```bash
vendor/bin/phpunit --testsuite Unit
```

## Notlar

- Generator komutlari tekrar calistiginda mevcut dosyalari kontrol ederek kullan.
- Uretim ortami icin migration akisini CI/CD uzerinden standardize et.
- `setup:check`, kurulum sonrasi DB/tablo/rol/permission ve HTTP smoke dogrulamasini tek komutta yapar.
