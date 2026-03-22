# Settings Strategy

Kirpi Framework cekirdek kararidir:

- Global ayarlar `.env` ve `config/*.php` uzerinden yonetilir.
- Cekirdekte DB tabanli dinamik settings tablosu bulunmaz.
- Uygulamaya ozgu runtime ayarlari gerekiyorsa ilgili proje bunu kendi modulunde kurar.

## Neden Bu Yapi?

- Deterministik davranis: deploy edilen ortam ne ise uygulama da odur.
- Basitlik: cekirdekte ekstra settings cache/invalidation karmasasi yok.
- Guvenlik: kritik ayarlar repository yerine ortama tanimli tutulur.

## Uygulama Gelistiriciye Not

- Ortama gore farkli degerler icin `.env.production`, `.env.staging` gibi dosyalar kullan.
- Sadece cekirdek seviyede tekrar kullanilacak ayarlari `config/*` dosyalarina ekle.
- Tek projeye ozgu parametreler uygulama modulunde kalmali.

