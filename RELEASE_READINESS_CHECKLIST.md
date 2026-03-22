# Release Readiness Checklist

Tarih: 2026-03-22

Bu dosya, "tamamlandi" demeden once son kontrol setini tek yerde toplar.

## 1) Kod ve Test

- [x] Unit testler gecti (`vendor/bin/phpunit --testsuite Unit`)
- [x] Kritik route sentaks kontrolleri temiz
- [x] App landing ve manager endpoint smoke testleri 200 donuyor

## 2) Backup ve Kurtarma

- [x] Manager Backup Center uzerinden `db` backup olusturuldu
- [x] Manager Backup Center uzerinden `full` backup olusturuldu
- [x] `verify` ile checksum dogrulandi
- [ ] Tam restore tatbikati (izole ortamda) tamamlandi
  Not: Operasyonel adim olarak planlanmali; canli veri uzerinde dogrudan uygulanmamali.

## 3) Manager Guvenligi

- [x] Manager token zorunlu
- [x] Manager API icin throttle aktif (`throttle:120,60`)
- [x] Opsiyonel IP allowlist (`KIRPI_MANAGER_IP_WHITELIST`)
- [x] Audit log kanali (`manager-audit`) ile giris denemeleri kayitli

## 4) Konfigurasyon ve Dokumantasyon

- [x] `.env.example` manager/backup ayarlari guncel
- [x] Kapsamli docs seti hazir ve Turkce
- [x] Landing sayfasinda GitHub dokumantasyon linki var

## 5) Docker Temizligi

- [x] Kullanilmayan container/image/network temizligi yapildi
- [x] Kullanilmayan volume temizligi yapildi
- [ ] Temizlik sonrasi backup dosyalarinin dis ortama alinmasi kontrol edildi

## 6) Surumleme

- [ ] Tag olusturuldu (ornek: `v1.0.0`)
- [ ] Release notu yayinlandi (`RELEASE_NOTES.md` standardina gore)

