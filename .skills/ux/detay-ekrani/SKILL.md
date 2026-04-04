---
name: detay-ekrani
description: Kirpi Framework içinde detay ekranlarının sade, okunabilir ve aksiyon odaklı şekilde oluşturulmasını sağlar.
---

# Amaç

Detay ekranı, bir kaydın tüm bilgilerini gösterir.

Amaç:

- bilgiyi net göstermek
- kullanıcıya güven vermek
- aksiyonlara erişim sağlamak

# Genel prensipler

## 1. Bilgi bölünmelidir

Bilgi:

- tek blokta verilmez
- kartlara ayrılır

## 2. Okunabilirlik önceliktir

- label + değer yapısı
- sade metin
- karmaşa yok

## 3. Aksiyonlar görünür olmalıdır

- düzenle
- listeye dön
- opsiyonel işlemler

# Zorunlu bileşenler

## 1. Page Header

- başlık (kayıt adı)
- açıklama
- aksiyonlar

## 2. Profil / Özet alanı

- avatar
- isim
- ana bilgi
- durum

## 3. Bilgi kartları

- genel bilgiler
- sistem bilgileri
- ek alanlar

## 4. Aktivite alanı (opsiyonel)

- geçmiş işlemler
- zaman bilgisi

# Layout kuralları

- sol: özet
- sağ: detay

# Yasaklar

- tek kolon uzun metin
- karmaşık grid
- gereksiz ikon kullanımı

# Çıktı formatı

1. amaç
2. veri grupları
3. layout
4. aksiyonlar
5. HTML

# Kabul kriterleri

- sade
- okunabilir
- Tabler uyumlu

# Kirpi notu

Detay ekranı, sistemin doğruluğunu gösterir.

Kullanıcı burada şunu hissetmelidir:

"Bu veri doğru ve kontrol edilebilir."