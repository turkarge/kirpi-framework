# Skill: Kirpi Testing & Release

## Kapsam

- test calistirma standardi
- release notlari
- tag ve final kontrol

## Test Standarti

- minimum: ilgili dosya sentaks + endpoint smoke
- genis kapsama: `vendor/bin/phpunit --testsuite Unit`
- test calistirilamiyorsa nedenini acik yaz

## Release Standarti

- `RELEASE_NOTES.md` formatina uy
- `RELEASE_READINESS_CHECKLIST.md` maddelerini kontrol et
- release tamamlandiginda tag isle

## Commit Kurali

- atomik commit
- konu odakli mesaj
- unrelated degisiklikleri ayni committe toplama
