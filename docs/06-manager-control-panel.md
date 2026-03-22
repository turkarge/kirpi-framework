# 06 - Manager Kontrol Paneli

Manager panel framework operasyon merkezidir.

## Giris

- `http://localhost:8081/manager?token=<KIRPI_MANAGER_TOKEN>`

## Ust Menu

- Core
- Modules
- Integrations
- Developer
- System

## Ana Ekranlar

- `/manager` : control dashboard
- `/manager/modules` : system modules
- `/manager/custom-modules` : wizard
- `/manager/mail` : mail test
- `/manager/tests` : test screen launcher
- `/manager/backup` : backup center

## Manager API Guvenligi

Tum manager API endpoint'leri `manager.token` middleware ile korunur.
Token `X-Manager-Token` header veya `?token=` ile gonderilir.
