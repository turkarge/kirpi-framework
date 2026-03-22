# 06 - Manager Control Panel

Manager panel framework operasyon merkezidir.

## Entry

- `http://localhost:8081/manager?token=<KIRPI_MANAGER_TOKEN>`

## Top Navigation

- Core
- Modules
- Integrations
- Developer
- System

## Main Screens

- `/manager` : control dashboard
- `/manager/modules` : system modules
- `/manager/custom-modules` : wizard
- `/manager/mail` : mail test
- `/manager/tests` : test screen launcher
- `/manager/backup` : backup center

## Manager API Security

Tumu `manager.token` middleware ile korunur.
Token `X-Manager-Token` header veya `?token=` ile gonderilir.
