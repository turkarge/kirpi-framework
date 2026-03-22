# 05 - Kullanim Temelleri

## App ve Manager Context

- `APP_CONTEXT=app`: standart uygulama runtime
- `APP_CONTEXT=manager`: manager route seti yuklenir

Docker kurulumu bu iki context'i ayri servis ile calistirir.

## Request Yasam Dongusu (ust seviye)

1. `public/index.php` bootstrap
2. container ve provider kaydi
3. route load
4. middleware pipeline
5. controller/closure dispatch
6. response send

## Routing

- app web routes: `routes/web.php`
- app api routes: `routes/api.php`
- manager routes: `routes/manager.php`
- module routes: `modules/*/routes/*.php`

## Modul Siniri

Cekirdege sadece tekrar kullanilabilir davranis eklenir.
Uygulamaya ozel davranis modulde kalir.
