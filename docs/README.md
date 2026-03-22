# Kirpi Framework Documentation

Kirpi Framework, kisisel ve kucuk/orta olcekli is uygulamalari icin sade bir PHP cekirdegidir.

## Documentation Map

- [01 - Introduction](./01-introduction.md)
- [02 - System Requirements](./02-system-requirements.md)
- [03 - Installation](./03-installation.md)
- [04 - Configuration](./04-configuration.md)
- [05 - Usage Basics](./05-usage-basics.md)
- [06 - Manager Control Panel](./06-manager-control-panel.md)
- [07 - Backup and Recovery](./07-backup-and-recovery.md)
- [08 - CLI Reference](./08-cli-reference.md)

## Philosophy

- Az sihir, net akis
- Moduler ama asiri soyut degil
- Framework degil, uygulama gelistirme hizini one cikarir
- Uygulamaya ozel ihtiyaclar cekirdege tasinmaz

## Quick Start

1. `cp .env.example .env`
2. `docker compose up -d --build`
3. `http://localhost` (landing)
4. `http://localhost:8081/manager?token=...` (manager)
