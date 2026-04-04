---
name: dashboard-ekrani
description: Kirpi Framework içinde dashboard ekranının sade, rol bazlı, modüler ve aksiyon odaklı olacak şekilde tasarlanmasını sağlar.
---

# Amaç

Dashboard, kullanıcının sisteme giriş yaptıktan sonra karşılaştığı ilk ekrandır.

Bu ekranın amacı:

- sistemin genel durumunu özetlemek
- kullanıcıya hızlı aksiyon sunmak
- önemli bilgileri görünür kılmak
- kullanıcıyı yönlendirmek

Dashboard, veri çöplüğü değildir.

# Kapsam

- KPI kartları
- widget sistemi
- hızlı işlemler
- sistem durumu
- aktiviteler
- rol bazlı görünüm
- layout davranışı

# Genel prensipler

## 1. Dashboard sade olmalıdır

Dashboard:

- karmaşık olmamalıdır
- okunabilir olmalıdır
- kullanıcıyı yormamalıdır

Az ama doğru bilgi gösterilir.

## 2. Dashboard aksiyon üretmelidir

Dashboard sadece bilgi vermez.

Aynı zamanda:

- işlem başlatır
- kullanıcıyı yönlendirir
- karar aldırır

## 3. Her kullanıcı aynı dashboard'u görmez

Dashboard:

- rol bazlıdır
- kullanıcıya göre değişir

Admin ile operatör aynı ekranı görmez.

# Zorunlu bileşenler

## 1. Page Header

Her dashboard ekranında standart page header bulunur:

- başlık (Dashboard)
- kısa açıklama
- sağda aksiyonlar (ör: Yeni Kayıt, Düzeni Sıfırla)

## 2. KPI kartları

Dashboard’un üst kısmında KPI kartları bulunur.

Özellikleri:

- sayısal veri içerir
- kısa başlık
- trend / durum bilgisi (artış, azalış, durum etiketi)

Örnekler:

- Toplam Kullanıcı
- Aktif Roller
- Günlük İşlem
- Bekleyen Görev

## 3. Hızlı işlemler

Kullanıcının sık yaptığı işlemler burada yer alır.

Özellikleri:

- kart veya buton şeklinde
- açık ve net aksiyon
- ikon destekli

Örnekler:

- Kullanıcı Ekle
- Rol Yönetimi
- Ayarlar

## 4. Sistem durumu

Sistem bileşenlerinin durumu gösterilir.

Örnekler:

- servis durumu
- queue worker
- e-posta sistemi

Durumlar:

- Online
- Hazır
- İzleniyor

## 5. Son işlemler

Kullanıcının veya sistemin yaptığı son işlemler listelenir.

Özellikleri:

- tablo formatı
- kısa ve net bilgi
- detay linki

## 6. Aktiviteler

Sistem içindeki önemli olaylar listelenir.

Özellikleri:

- zaman bilgisi
- kısa açıklama
- kronolojik sıra

# Widget sistemi

Dashboard statik değildir.

## 1. Widget mantığı

Dashboard bileşenleri widget olarak tanımlanır.

Her widget:

- bağımsızdır
- yeniden kullanılabilir
- kaldırılabilir / eklenebilir

## 2. Drag & Drop

Kullanıcı:

- widget’ları yeniden sıralayabilir

Bu bilgi:

- session veya kullanıcı ayarında saklanır

## 3. Rol bazlı widget

Her rol için farklı widget seti olabilir.

Örnek:

- admin → sistem durumu + kullanıcı + loglar
- operatör → işlem yoğunluğu + görevler

# Layout kuralları

## 1. Grid sistemi

Dashboard:

- Tabler grid sistemi ile oluşturulur
- responsive olmalıdır

## 2. Üstten alta akış

Sıralama:

1. KPI kartları
2. hızlı işlemler / durum
3. detay içerikler

## 3. Kart yapısı

Her içerik:

- card içinde olmalıdır
- başlık içermelidir
- padding standardına uymalıdır

# Yasaklar

- dashboard’ı veri çöplüğüne çevirmek
- 10+ KPI göstermek
- gereksiz grafik eklemek
- rol farkını yok saymak
- aksiyon sunmayan içerik eklemek
- widget mantığını bozmak

# Uygulama akışı

## Aşama 1

Kullanıcı rolünü belirle

## Aşama 2

Gösterilecek KPI’ları seç

## Aşama 3

Hızlı aksiyonları tanımla

## Aşama 4

Widget setini oluştur

## Aşama 5

Grid yerleşimini planla

## Aşama 6

Mobil görünümü kontrol et

# Çıktı formatı

Model şu sırayla cevap vermelidir:

1. dashboard amacı
2. KPI listesi
3. widget listesi
4. layout planı
5. rol farkları
6. HTML yapı

# Kabul kriterleri

- sade olmalı
- aksiyon içermeli
- rol bazlı olmalı
- Tabler uyumlu olmalı
- widget mantığına uygun olmalı

# Kirpi notu

Dashboard bir vitrin değildir.

Dashboard bir kontrol panelidir.

Kullanıcı buraya baktığında şu hissi yaşamalıdır:

"Sistem benim kontrolümde."