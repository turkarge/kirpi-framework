---
name: liste-ekrani
description: Kirpi Framework içinde liste ekranlarının tutarlı, aksiyon odaklı, filtrelenebilir ve Tabler uyumlu şekilde geliştirilmesini sağlar.
---

# Amaç

Bu skill, Kirpi Framework içindeki tüm liste ekranlarının aynı UX dili ile oluşturulmasını sağlar.

Amaç; kullanıcıya veriyi görüntüleme, filtreleme, değerlendirme ve aksiyon alma imkanı sunan sade ve güçlü liste ekranları üretmektir.

# Kapsam

Bu skill aşağıdaki liste ekranlarını kapsar:

- kullanıcı listesi
- rol listesi
- ayarlar listesi
- kayıt listeleri
- modül bazlı yönetim listeleri

# Genel prensipler

## 1. Liste ekranı karar ekranıdır

Liste ekranı yalnızca veri göstermez.

Aynı zamanda:

- kullanıcıyı yönlendirir
- filtreleme sağlar
- aksiyon üretir
- karar aldırır

## 2. Liste ekranı sade olmalıdır

Liste ekranı:

- okunabilir olmalıdır
- kalabalık olmamalıdır
- gereksiz bileşen içermemelidir

## 3. Ana aksiyon görünür olmalıdır

Her liste ekranında birincil aksiyon açıkça görünmelidir.

Örnek:

- Yeni Kullanıcı
- Yeni Rol
- Yeni Kayıt

# Zorunlu bileşenler

## 1. Page Header

Her liste ekranında standart page header bulunur:

- page pretitle
- page title
- kısa açıklama
- sağ alanda aksiyonlar

## 2. Primary Action

Header sağ tarafında birincil aksiyon bulunmalıdır.

Bu aksiyon:

- buton olmalıdır
- açık ve net isim taşımalıdır
- kullanıcının en sık yaptığı işlemi temsil etmelidir

Örnek:

- Yeni Kullanıcı
- Yeni Rol
- Yeni Kayıt

## 3. Aksiyon Menüsü

Header sağ tarafında, birincil aksiyonun yanında opsiyonel aksiyon menüsü bulunabilir.

Bu menü şu tür işlemleri taşıyabilir:

- içe aktar
- dışa aktar
- şablon indir
- listeyi yenile

Bu menü:

- ikincil işlemler için kullanılır
- primary action yerine geçmez

## 4. Filtre Alanı

Liste ekranında filtre alanı bulunmalıdır.

Filtre alanı şu öğeleri içerebilir:

- metin arama
- rol / kategori seçimi
- durum seçimi
- tarih filtresi
- filtrele butonu

Filtre alanı sade tutulmalıdır.

## 5. Veri Tablosu

Liste ekranının ana içeriği tablodur.

Tablo:

- okunabilir olmalıdır
- satır yoğunluğu dengeli olmalıdır
- Tabler table yapısına uygun olmalıdır

## 6. Satır içi aksiyonlar

Her kayıt için satır içi aksiyon alanı bulunabilir.

Bu alan genellikle dropdown menü ile sunulur.

Örnek:

- Düzenle
- Yetkiler
- Aktifleştir
- Sil

Satır aksiyonları tabloyu kalabalıklaştırmamalıdır.

## 7. Footer / Pagination

Liste ekranı altında şu alanlar bulunmalıdır:

- toplam kayıt sayısı
- pagination

# Görsel kurallar

## 1. Tabler uyumu zorunludur

Liste ekranı:

- Tabler card yapısı
- Tabler table yapısı
- Tabler form bileşenleri
- Tabler dropdown yapısı

ile oluşturulmalıdır.

## 2. Header sade olmalıdır

Liste ekranı header alanında:

- gereksiz ikinci satır aksiyonları
- ikon yığını
- çoklu buton karmaşası

olmamalıdır.

## 3. Badge kullanımı kontrollü olmalıdır

Durum göstergeleri için badge kullanılabilir.

Örnek:

- Aktif
- Pasif
- Bekliyor

Ama badge sayısı abartılmamalıdır.

# Davranış kuralları

## 1. Liste ekranı tek bakışta anlaşılmalıdır

Kullanıcı ekrana geldiğinde hemen anlamalıdır:

- hangi kayıtları görüyor
- nasıl filtreleyecek
- nasıl yeni kayıt ekleyecek
- bir satır üzerinde ne yapabilecek

## 2. Aksiyonlar önceliklendirilmelidir

Aksiyon sıralaması:

1. primary action
2. aksiyon menüsü
3. satır içi aksiyonlar

## 3. Boş durum düşünülmelidir

Kayıt yoksa ekran boş bırakılmaz.

Boş durumda:

- kısa açıklama
- yönlendirici metin
- primary action

gösterilmelidir.

## 4. Mobil davranış zorunludur

Mobilde:

- filtre alanı düzgün kırılmalıdır
- tablo taşma yapmamalıdır
- satır aksiyonları erişilebilir olmalıdır

# Yasaklar

- gereksiz çok sayıda filtre alanı
- birden fazla primary action
- satır içine çok sayıda buton koymak
- tabloyu badge ve ikon çöplüğüne çevirmek
- import/export işlemlerini primary action gibi göstermek
- pagination olmadan uzun liste göstermek
- boş durumda kullanıcıyı yönsüz bırakmak

# Uygulama akışı

## Aşama 1

Liste konusu belirlenir:

- hangi kayıtlar gösterilecek?
- kullanıcının temel amacı nedir?

## Aşama 2

Page header hazırlanır:

- başlık
- açıklama
- primary action
- aksiyon menüsü

## Aşama 3

Filtre alanı hazırlanır:

- arama
- seçim alanları
- filtrele aksiyonu

## Aşama 4

Tablo oluşturulur:

- kolonlar
- satır içerikleri
- durum alanları
- aksiyon dropdown

## Aşama 5

Footer hazırlanır:

- kayıt sayısı
- pagination

## Aşama 6

Boş durum ve mobil görünüm kontrol edilir

# Çıktı formatı

Model şu sırayla cevap vermelidir:

1. liste amacı
2. header yapısı
3. filtre alanı
4. tablo kolonları
5. satır aksiyonları
6. footer / pagination
7. HTML yapı

# Kabul kriterleri

- page header açık olmalı
- primary action görünür olmalı
- aksiyon menüsü düzenli olmalı
- filtre alanı sade olmalı
- tablo okunabilir olmalı
- satır içi aksiyonlar dropdown ile sunulmalı
- pagination bulunmalı
- Tabler uyumlu olmalı

# Kirpi notu

Liste ekranı, kullanıcının sistemi taradığı ve aksiyon aldığı ana yüzeylerden biridir.

Her liste ekranı için şu soru sorulmalıdır:

"Kullanıcı bu ekrana geldiğinde neyi gördüğünü, ne yapabileceğini ve nasıl ilerleyeceğini hemen anlayabiliyor mu?"