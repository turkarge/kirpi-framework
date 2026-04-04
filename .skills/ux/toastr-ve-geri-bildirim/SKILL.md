---
name: toastr-ve-geri-bildirim
description: Kirpi Framework içinde toastr tabanlı kullanıcı geri bildirimlerinin tutarlı, kısa ve anlamlı şekilde kullanılmasını sağlar.
---

# Amaç

Bu skill, Kirpi Framework içinde kullanıcıya gösterilen anlık geri bildirimlerin standartlaştırılmasını sağlar.

Amaç:

- işlem sonucunu görünür kılmak
- kullanıcıyı belirsizlikte bırakmamak
- başarı, hata, uyarı ve bilgi mesajlarını aynı dilde sunmak

# Kapsam

Bu skill aşağıdaki geri bildirim tiplerini kapsar:

- başarı bildirimi
- hata bildirimi
- uyarı bildirimi
- bilgi bildirimi
- progress bar
- close button
- sticky toast
- duplicate prevention
- çoklu bildirim akışı

# Genel prensipler

## 1. Toastr sonuç bildirir

Toastr:

- kullanıcıya işlem sonucunu söyler
- kısa yaşar
- ana ekranın yerine geçmez

Uzun açıklamalar için kullanılmaz.

## 2. Mesaj kısa ve net olmalıdır

Başlık ve içerik kısa olmalıdır.

Doğru örnek:

- Kullanıcı başarıyla kaydedildi.
- İşlem sırasında hata oluştu.
- Rapor oluşturma başlatıldı.

## 3. Bildirim türü doğru seçilmelidir

### Success
Başarılı işlem sonrasında kullanılır.

### Error
İşlem başarısız olduğunda kullanılır.

### Warning
Riskli veya dikkat gerektiren durumlarda kullanılır.

### Info
Bilgilendirme veya arka plan süreci için kullanılır.

# Zorunlu kurallar

## 1. Varsayılan konum standart olmalıdır

Kirpi’de varsayılan toastr konumu:

- `toast-top-right`

Alternatif konum yalnızca özel senaryolarda kullanılabilir.

## 2. Başarı mesajları net olmalıdır

Başarı mesajları:

- kısa
- olumlu
- sonuca odaklı

olmalıdır.

Örnek:

- Kayıt başarıyla oluşturuldu.
- Değişiklikler kaydedildi.

## 3. Hata mesajları kullanıcıya yön vermelidir

Hata mesajı yalnızca “bir şey oldu” demez.

Mümkünse:

- neyin başarısız olduğu
- ne yapılması gerektiği

anlaşılır şekilde belirtilir.

## 4. Sticky toast dikkatli kullanılmalıdır

Sabit bildirim sadece şu durumlarda kullanılır:

- kritik uyarı
- kullanıcı onayı bekleyen bilgi
- mutlaka görülmesi gereken mesaj

Her mesaj sticky yapılmaz.

## 5. Duplicate prevention desteklenmelidir

Aynı mesajın art arda birden fazla gösterilmesi engellenmelidir.

Özellikle:

- çift tıklama
- tekrar eden event
- form submit spam

durumlarında önemlidir.

## 6. Progress bar desteklenmelidir

Zamanlı bildirimlerde progress bar kullanılabilir.

Bu, kullanıcıya bildirimin geçici olduğunu hissettirir.

## 7. Close button gerektiğinde açılmalıdır

Kapatma butonu şu durumlarda önerilir:

- sticky toast
- uyarı bildirimi
- daha uzun süre gösterilen bilgi mesajı

## 8. HTML içerik kontrollü kullanılmalıdır

HTML içerik yalnızca gerçekten gerektiğinde kullanılmalıdır.

Amaç:

- vurgu
- satır kırılımı
- kısa zengin içerik

Uzun HTML yapıları toastr içine taşınmaz.

# Davranış kuralları

## 1. Toastr sayfa akışını bozmamalıdır

Bildirim:

- ekranı kaplamaz
- kullanıcıyı engellemez
- geçici olarak görünür

## 2. Toastr ana bilgi kaynağı değildir

Kritik doğrulamalar:

- form alanı altında
- sayfa içinde
- modal içinde

çözülmelidir.

Toastr sadece destekleyici rol oynar.

## 3. Çoklu bildirimler kontrol edilmelidir

Aynı anda çok fazla toastr açılmaz.

Sistem kullanıcıyı bombardımana tutmaz.

## 4. Başlık zorunlu değildir ama desteklenir

Her toastr başlık içermek zorunda değildir.

Ama daha net bir deneyim için kullanılabilir.

# Yasaklar

- uzun paragraf halinde mesaj göstermek
- kritik form hatalarını sadece toastr ile vermek
- her işlemi sticky toast yapmak
- aynı mesajı tekrar tekrar göstermek
- tüm akışı toastr’a taşımak
- başarı/hata türlerini yanlış kullanmak

# Uygulama akışı

## Aşama 1

İşlem türünü belirle:

- başarı
- hata
- uyarı
- bilgi

## Aşama 2

Mesajı yaz:

- kısa
- açık
- anlaşılır

## Aşama 3

Gerekliyse ek davranış seç:

- close button
- progress bar
- sticky
- duplicate prevention

## Aşama 4

Konumu ve süreyi kontrol et

# Çıktı formatı

Model şu sırayla cevap vermelidir:

1. bildirim amacı
2. bildirim türü
3. mesaj metni
4. ek davranışlar
5. toastr kodu

# Kabul kriterleri

- mesaj kısa olmalı
- tür doğru seçilmeli
- kullanıcıyı yormamalı
- Tabler / Kirpi UI ile uyumlu olmalı
- kritik bilgi için yanlış kullanılmamalı

# Kirpi notu

Toastr, sistemin kullanıcıyla göz kırptığı andır.

Kısa olmalı.
Net olmalı.
Rahatsız etmeden anlaşılmalı.