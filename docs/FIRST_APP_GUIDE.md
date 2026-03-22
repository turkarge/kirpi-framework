# First App Guide

Bu rehber, Kirpi ile ilk uygulamayi hizli baslatmak icindir.

## 1. Ortami Hazirla

1. `.env.example` dosyasini kopyala ve `.env` olustur.
2. DB/Redis bilgilerini kendi ortamına gore duzenle.
3. Servisleri kaldir:

```bash
docker compose up -d
```

## 2. Cekirdek Hazirlik

1. Uygulama anahtarini uret:

```bash
php framework key:generate
```

2. Migration calistir:

```bash
php framework migrate
```

## 3. Modul Iskeleti Uret

```bash
php framework make:module Catalog
```

Bu komut su klasorleri uretir:

- `modules/Catalog/Controllers`
- `modules/Catalog/Models`
- `modules/Catalog/Views`
- `modules/Catalog/routes`

## 4. CRUD Baslangici Uret

```bash
php framework make:crud Catalog Product
```

Bu komut:

- `Product` modeli
- `ProductController` CRUD methodlari
- temel view dosyalari
- `modules/Catalog/routes/web.php` icine admin resource route

ekler.

## 5. Route Adlandirma

Scaffold edilen admin resource route'lari named route ile gelir:

- `admin.products.index`
- `admin.products.create`
- `admin.products.store`
- `admin.products.show`
- `admin.products.edit`
- `admin.products.update`
- `admin.products.destroy`

Kod tarafinda:

```php
$url = route('admin.products.index');
```

## 6. Guvenlik Notu

- Admin route'lar `adminResource` ile uretilir.
- Varsayilan middleware zinciri: `auth` + `permission:admin-access`.
- Uretimde acik admin endpoint birakma.

