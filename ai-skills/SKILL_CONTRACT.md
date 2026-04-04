# SKILL CONTRACT - Kirpi Framework

Bu sozlesme, Kirpi Framework uzerinde calisan tum AI editorlerin uymasi gereken zorunlu kurallari tanimlar.

## 1) Kimlik

Kirpi Framework:
- kisisel/ozel uygulamalar icin sade cekirdek
- frameworku buyutmek degil, uygulama gelistirmeyi hizlandirmak
- tekrar kullanilmayan seyi cekirdege eklememek

## 2) Mimari Karar Kriteri

Her degisiklikte su 5 soru zorunlu:
1. Sorun ne?
2. Neden sorun?
3. En sade dogru cozum ne?
4. Bu cozum cekirdege mi uygulama katmanina mi ait?
5. Artisi/eksisi ne?

## 3) Kod Kurallari

- PHP 8.4 uyumlu
- okunabilirlik > clever kod
- gereksiz abstraction yok
- magic/facade coplugu yok
- public API degisiklikleri acikca belirtilmeli

## 4) Guvenlik

- debug/backdoor birakilmaz
- token/secrets loglanmaz
- manager endpointleri token ile korunur
- gerekiyorsa IP allowlist ve throttle uygulanir

## 5) Frontend Kurali

- Varsayilan UI dili Tabler
- yeni UI parcalari Tabler component semantigine uyumlu olmali
- custom CSS, Tabler'i bozmayacak minimum seviyede kalmali

Tabler kaynaklari:
- https://github.com/tabler/tabler
- https://preview.tabler.io/

## 6) Test ve Dogrulama

- degisiklikten sonra en az ilgili testler calistirilir
- kritik degisiklikte `vendor/bin/phpunit --testsuite Unit`
- calisamayan test varsa sebep acik yazilir

## 7) Release Disiplini

- release not standardi: `RELEASE_NOTES.md`
- final kontrol listesi: `RELEASE_READINESS_CHECKLIST.md`
- stabil yayinlarda tag kullanilir (ornek: `v1.0.0`)

## 8) Tek Kaynak Kurali

Editor ozel dosyalar (CLAUDE, Copilot, Cursor vb.) bu sozlesmeyle celisemez.
Celiski olursa bu dosya kazanir.
