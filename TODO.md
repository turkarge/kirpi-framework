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
- [x] Frontend component stillerini tek bir ortak stylesheet'e tasi
- [x] Demo sayfasini Tabler `layout-fluid` kaynagina bagla (vendor template tabanli)

## M6 - Kirpi Native Bildirim Sistemi (Acik)
- [x] Global notify API (`window.kirpiNotify`) + toast UI altyapisi
- [x] Backend flash/session mesajlarini otomatik toast'a bagla
- [x] API response message standardini notify katmanina otomatik haritala

## M7 - Frontend Deneyim Paketi (Planlandi)
- [x] Tabler tema entegrasyonu (layout + admin demo + ui-kit)
- [x] UI sayfalarini tek Tabler shell uzerinde birlestir (ui-kit + notify + api-notify + demo)
- [x] Legacy admin layout/template kalintilarini kaldir
- [x] Navbar patch katmani (Kirpi route menusu + aktif menu + source/sponsor/app-menu temizligi)
- [x] UI componentlerini Tabler standartlarina gore normalize et (button/card/form/table)
- [x] Notify toast gorunumunu Tabler tasarim diline yaklastir
- [x] Responsive grid ve mobil kirilim kurallari (admin-demo + ui-kit)
- [x] Tema sistemi: light/dark mode toggle + kalici tercih (localStorage)
- [x] PWA tabani: manifest + service worker + offline fallback sayfasi
- [x] Merkezi modal sistemi (`window.kirpiModal`) + modal test sayfasi
- [x] Import/Export UI akisi (CSV/Excel) icin temel bilesenler + test sayfasi
- [x] Empty/Loading/Error state bilesen seti + test sayfasi
- [x] Keyboard kisayollari ve temel erisilebilirlik (focus/aria) iyilestirmeleri
- [x] Frontend feature'lari icin "her ozellige bir test sayfasi" standardini kalici hale getir

## M9 - Tabler UI Stabilizasyonu (Siradaki)
- [x] Navbar user menu ve bildirim icerigini kirilgan regex olmadan guvenli patch yaklasimina tasi
- [x] Kirpi layout patch adimlarini tek bir "layout transformer" sinifina ayir
- [x] UI smoke testi: ortak Tabler shell transformer/parts icin unit smoke testleri ekle
- [x] TODO/roadmap guncellemeleri icin release not standardi belirle

## M8 - AI Destek Katmani (Planlandi)
- [x] AI servis adapter arayuzu (provider-agnostic, cekirdek seviyede minimal)
- [x] Prompt log/trace kayit modeli (debug odakli, gizli veri filtreli)
- [x] Uygulama katmaninda kullanilacak AI action endpoint ornekleri
- [x] AI ozellikleri icin kapatilabilir feature flag yapisi
- [x] Yerel model bagimliligini kaldirip dis servis provider yapisina gecis (OpenAI/Anthropic)
- [ ] OpenAI canli kullanim dogrulamasi (blokaj: `429 insufficient_quota`, billing/plan sonrasi tekrar test edilecek)

## M10 - Uygulama Gelistirme Akisi ve DX (Siradaki)
- [x] CLI module generator (`framework make:module`) ile standart modul iskeleti (Controller/Model/routes/views) uret
- [x] Admin CRUD scaffold (`framework make:crud`) ile listele/ekle/duzenle/sil hizli baslangic akisi ekle
- [x] Route adlandirma ve URL helper standardini netlestir (`route()`, named routes) ve kontrat testleri yaz
- [x] Admin route'lar icin policy/middleware zorunlulugu (auth + yetki) getir, acik endpoint kalmasin
- [x] Settings mimarisi karari dokumante et: global tanimlar `.env` + `config/*` uzerinden devam (DB tabanli ayar yok)
- [x] Uretim hata sayfalari (404/500) ve request-id/log korelasyonunu standartlastir
- [x] Generator komutlari ve modul gelistirme akisi icin "ilk uygulama" rehberi hazirla

## M11 - Kurulum Otomasyonu (Siradaki)
- [x] `framework setup` kurulum sihirbazi (local/cloud profili, env yazimi, ilk admin)
- [x] `framework setup:admin` idempotent admin olusturma/guncelleme komutu
- [x] Lokal kurulumda docker up + migrate + admin bootstrap akisi
- [x] Kurulum sonu health/ready kontrolu ve rapor kaydi (`storage/setup/*.json`)
