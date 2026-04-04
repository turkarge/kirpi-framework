---
name: ayarlar-ekrani
description: Kirpi Framework içinde ayarlar ekranlarının sekmeli, düzenli ve sistem davranışını yöneten yapıda oluşturulmasını sağlar.
---

# Amaç

Bu skill, Kirpi Framework içindeki ayarlar ekranlarının tutarlı, düzenli ve yönetilebilir şekilde oluşturulmasını sağlar.

Amaç:

- sistem davranışını yapılandırmak
- ayarları kategorize etmek
- kullanıcıyı uzun ve dağınık formlar içinde kaybetmemek

# Kapsam

Bu skill aşağıdaki ayar ekranlarını kapsar:

- genel ayarlar
- marka ayarları
- görünüm ayarları
- e-posta ayarları
- güvenlik ayarları
- modül bazlı ayar ekranları

# Genel prensipler

## 1. Ayarlar ekranı düz form değildir

Ayar ekranı:

- kategorilere ayrılmalıdır
- sekmeli veya gruplu yapıda sunulmalıdır
- tek uzun form halinde verilmemelidir

## 2. Ayarlar sistem davranışını temsil eder

Bu ekran sıradan veri girişi değildir.

Ayar ekranı:

- uygulamanın nasıl çalışacağını belirler
- görsel ve davranışsal kararları yönetir
- dikkatli sunulmalıdır

## 3. Kategoriler açık olmalıdır

Ayarlar şu gibi açık başlıklara ayrılmalıdır:

- Genel
- Marka
- Görünüm
- E-posta
- Güvenlik

# Zorunlu bileşenler

## 1. Page Header

Her ayar ekranında:

- başlık
- kısa açıklama
- kaydet aksiyonu
- opsiyonel varsayılana dön aksiyonu

yer almalıdır.

## 2. Sol kategori menüsü

Ayar kategorileri sol tarafta listelenmelidir.

Bu alan:

- sade olmalıdır
- ikon destekli olabilir
- mevcut sekmeyi açıkça göstermelidir

## 3. Sağ içerik alanı

Seçili kategoriye ait ayarlar sağ tarafta gösterilmelidir.

Her kategori kendi card yapısı içinde sunulmalıdır.

## 4. Kaydet aksiyonu

Kaydet aksiyonu görünür olmalıdır.

Kritik ayarlar için:

- ayrı test butonu
- ayrı doğrulama aksiyonu

bulunabilir.

Örnek:

- Test E-postası Gönder

## 5. Önizleme alanı

Marka, logo veya görünüm gibi ayarlarda gerekiyorsa önizleme alanı bulunmalıdır.

# Görsel kurallar

## 1. Sekmeli / kategorili yapı zorunludur

Ayar ekranı tek parça uzun form olarak tasarlanmaz.

## 2. Alanlar mantıksal gruplara ayrılmalıdır

Örnek:

- uygulama adı, dil, zaman dilimi
- logo, favicon
- tema modu, yoğunluk
- smtp ayarları
- güvenlik seçenekleri

## 3. Switch kullanımı kontrollü olmalıdır

Aç / kapat tipi ayarlarda switch kullanılabilir.

Ama her alan switch’e çevrilmez.

# Davranış kuralları

## 1. Kritik ayarlar destek açıklaması içermelidir

Sistem davranışını etkileyen alanlarda kısa destek metni verilebilir.

## 2. Test edilebilir alanlar desteklenmelidir

E-posta gibi alanlarda test aksiyonu sunulabilir.

## 3. Görünüm ayarları sistemle uyumlu olmalıdır

Özellikle:

- logo
- tema modu
- yoğunluk

gibi ayarlar sistem davranışıyla bağlantılı düşünülmelidir.

## 4. Varsayılanlara dön aksiyonu dikkatli kullanılmalıdır

Bu aksiyon:

- görünür olabilir
- ama primary action ile karışmamalıdır

# Yasaklar

- ayarları tek uzun form halinde vermek
- kategori mantığını kaldırmak
- açıklamasız kritik alan kullanmak
- gereksiz kalabalık oluşturmak
- test gerektiren alanlarda kullanıcıyı kör bırakmak

# Uygulama akışı

## Aşama 1

Ayar kategorileri belirlenir

## Aşama 2

Sol kategori menüsü oluşturulur

## Aşama 3

Her kategori için card bazlı içerik hazırlanır

## Aşama 4

Kaydet ve yardımcı aksiyonlar eklenir

## Aşama 5

Önizleme ve test alanları gerekiyorsa eklenir

## Aşama 6

Mobil davranış kontrol edilir

# Çıktı formatı

Model şu sırayla cevap vermelidir:

1. ayar ekranı amacı
2. kategori listesi
3. kategori içerikleri
4. aksiyonlar
5. HTML yapı

# Kabul kriterleri

- kategorili yapı olmalı
- sol menü net olmalı
- içerik sade olmalı
- kaydet aksiyonu görünür olmalı
- Tabler uyumlu olmalı

# Kirpi notu

Ayar ekranı, sistemin dümenidir.

Kullanıcı burada şunu hissetmelidir:

"Ne değiştirdiğimi biliyorum ve bunu güvenle yönetebiliyorum."