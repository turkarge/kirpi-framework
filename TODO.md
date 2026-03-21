# TODO - Kirpi Framework

Bu liste aktif backlog'dur. Her adim tamamlandikca guncellenecek.

## M1 - Runtime ve Operasyon Gorunurlugu (Tamamlandi)
- [x] `/kirpi` runtime paneli
- [x] DB/Cache canli rozetleri
- [x] Self-check endpoint (`/kirpi/self-check`)
- [x] Self-check butonu ve panelde sonuc gosterimi
- [x] Self-check history endpoint (`/kirpi/self-check/history`, son 20)
- [x] Readiness endpoint (`/ready`, healthy=200, degraded=503)
- [x] `/kirpi` panelinde history icin son 5 sonucu kart olarak goster
- [x] Self-check sonucuna latency trendi ekle
- [x] Runtime paneline "copy diagnostics" butonu ekle

## M2 - Cekirdek Mimari Sadelestirme ve Guvenlik Aglari (Tamamlandi)
- [x] ServiceProvider tabani
- [x] Bootstrap provider lifecycle sadelestirme
- [x] Provider listesi config uzerinden yukleme
- [x] Support provider'i parcalama (support/communication/monitoring)
- [x] Monitoring provider feature flag (`KIRPI_FEATURE_MONITORING`)
- [x] Communication provider feature flag (`KIRPI_FEATURE_COMMUNICATION`)
- [x] Monitoring route registration gate
- [x] Communication helper guard mesajlari
- [x] Runtime/self-check logic'ini route dosyasindan `Core\\Runtime\\RuntimeDiagnostics` servisine tasi
- [x] `/kirpi` HTML render'ini route closure yerine tek bir `RuntimeController` altina al
- [x] Runtime endpointleri icin JSON contract testlerini ayri test sinifina bol
- [x] CI pipeline'a docker test adimi ekle

## M3 - Sonraki Iterasyon (Acik)
- [x] Runtime dashboard HTML/CSS/JS icerigini controller icinden ayri bir template dosyasina al
- [x] Runtime endpointleri icin snapshot-benzeri fixture tabanli kontrat testi ekle
- [x] Test altyapisindaki encoding/kirli yorum satirlarini temizle (`tests/Support/TestCase.php`)

## M4 - Uretim Saglik Kurallari ve Sonraki Adimlar (Acik)
- [x] `/ready` endpointi icin net kural seti (servis up + latency esikleri) ve degrade reason listesi
- [x] `/kirpi` runtime panelini `APP_ENV=production` icin opsiyonel kapatma/koruma
- [x] Frontend milestone: temel admin layout + reusable component yapisi (button/card/form/table)

## M5 - Frontend Uygulama Iskeleti (Acik)
- [x] Admin demo sayfasi (sidebar + topbar + kpi + form + table) ekle
- [ ] Frontend component stillerini tek bir ortak stylesheet'e tasi
- [ ] Demo sayfasi veri kaynaklarini (dummy) controller'dan ayri `ViewModel` sinifina al
