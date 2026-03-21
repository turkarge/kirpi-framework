# TODO - Kirpi Framework

Bu liste aktif backlog'dur. Her adim tamamlandikca guncellenecek.

## Runtime / Ops
- [x] `/kirpi` runtime paneli
- [x] DB/Cache canli rozetleri
- [x] Self-check endpoint (`/kirpi/self-check`)
- [x] Self-check butonu ve panelde sonuc gosterimi
- [x] Self-check history endpoint (`/kirpi/self-check/history`, son 20)
- [x] Readiness endpoint (`/ready`, healthy=200, degraded=503)

## Feature Gates
- [x] Monitoring provider feature flag (`KIRPI_FEATURE_MONITORING`)
- [x] Communication provider feature flag (`KIRPI_FEATURE_COMMUNICATION`)
- [x] Monitoring route registration gate
- [x] Communication helper guard mesajlari

## Architecture
- [x] ServiceProvider tabani
- [x] Bootstrap provider lifecycle sadelestirme
- [x] Provider listesi config uzerinden yukleme
- [x] Support provider'i parcalama (support/communication/monitoring)

## Next
- [x] `/kirpi` panelinde history icin son 5 sonucu kart olarak goster
- [x] Self-check sonucuna latency trendi ekle
- [x] Runtime paneline "copy diagnostics" butonu ekle
- [ ] CI pipeline'a docker test adimi ekle
