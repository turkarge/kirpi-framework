# 08 - CLI Referansi

## Cekirdek Komutlar

```bash
php framework make:module Catalog
php framework make:crud Catalog Product
php framework migrate
php framework migrate:rollback
php framework cache:clear
```

## Test

```bash
vendor/bin/phpunit --testsuite Unit
```

## Notlar

- Generator komutlari tekrar calistiginda mevcut dosyalari kontrol ederek kullan.
- Uretim ortami icin migration akisini CI/CD uzerinden standardize et.
