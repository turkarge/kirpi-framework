# Kirpi Framework Dokumantasyonu

Kirpi Framework, kisisel ve kucuk/orta olcekli is uygulamalari icin sade bir PHP cekirdegidir.

## Dokuman Haritasi

- [01 - Giris](./01-introduction.md)
- [02 - Sistem Gereksinimleri](./02-system-requirements.md)
- [03 - Kurulum](./03-installation.md)
- [04 - Konfigurasyon](./04-configuration.md)
- [05 - Kullanim Temelleri](./05-usage-basics.md)
- [06 - Manager Kontrol Paneli](./06-manager-control-panel.md)
- [07 - Yedekleme ve Geri Donus](./07-backup-and-recovery.md)
- [08 - CLI Referansi](./08-cli-reference.md)
- [09 - Local Setup Dogrulama](./09-local-setup-verification.md)

## Temel Yaklasim

- Az sihir, net akis
- Moduler ama gereksiz soyutlama yok
- Framework degil, uygulama gelistirme hizi odakta
- Uygulamaya ozel ihtiyac cekirdege tasinmaz

## Hizli Baslangic

1. `cp .env.example .env`
2. `php framework setup --profile=local`
3. `http://localhost` (landing)
4. `http://localhost/health`
5. `http://localhost:8081/manager/api/overview?token=...`
