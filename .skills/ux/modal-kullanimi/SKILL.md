---
name: modal-kullanimi
description: Kirpi Framework içinde modalların sade, amaç odaklı ve doğru boyutta kullanılmasını sağlar.
---

# Amaç

Bu skill, Kirpi Framework içindeki modal kullanımını standartlaştırır.

Amaç; kullanıcıyı sayfadan koparmadan kısa akışları yönetmek, ama modal kullanımını kontrolsüz hale getirmemektir.

# Kapsam

Bu skill aşağıdaki modal türlerini kapsar:

- küçük modal
- orta modal
- büyük modal

# Genel prensipler

## 1. Modal bir araçtır, sayfanın yerine geçmez

Modal:

- kısa akışlar için kullanılır
- kullanıcıyı destekler
- ana ekranın yerine geçmez

Uzun ve çok aşamalı süreçler modal içine taşınmaz.

## 2. Her modal tek bir amaca sahip olmalıdır

Bir modal içinde tek ana amaç bulunmalıdır.

Örnekler:

- onay almak
- kısa form göstermek
- hızlı düzenleme yapmak

## 3. Modal boyutu ihtiyaca göre seçilmelidir

### Küçük modal
Kullanım alanı:

- onay
- kısa uyarı
- tek karar

### Orta modal
Kullanım alanı:

- kısa form
- seçim ekranı
- tek adımlı işlem

### Büyük modal
Kullanım alanı:

- daha detaylı form
- önizleme + düzenleme
- çok alanlı ama hâlâ tek bağlamlı akış

# Zorunlu kurallar

## 1. Modal başlığı açık olmalıdır

Modal başlığı:

- kısa
- ne olduğunu anlatan
- aksiyonu tanımlayan

olmalıdır.

Örnekler:

- Silme Onayı
- Rol Atama
- Kullanıcı Düzenle

## 2. Modal footer standardı korunmalıdır

Footer’da:

- sol tarafta iptal / kapat
- sağ tarafta ana aksiyon

olmalıdır.

## 3. Primary action tek olmalıdır

Her modal içinde bir adet ana aksiyon bulunmalıdır.

Örnek:

- Kaydet
- Güncelle
- Sil
- Onayla

## 4. Modal içeriği sade olmalıdır

İçerik:

- kısa olmalıdır
- gereksiz alan içermemelidir
- kullanıcıyı boğmamalıdır

## 5. Modal boyutu gereksiz büyütülmez

Büyük modal yalnızca gerçekten gerekli olduğunda kullanılır.

Sırf ferah görünsün diye büyük modal açılmaz.

## 6. Kapatma davranışı net olmalıdır

Her modalda:

- kapatma butonu
- iptal aksiyonu

bulunmalıdır.

# Davranış kuralları

## 1. Küçük modal

Küçük modal:

- onay ve uyarı için kullanılır
- form içermez ya da minimum alan içerir

## 2. Orta modal

Orta modal:

- kısa form için ana standarttır
- varsayılan modal boyutu olarak tercih edilebilir

## 3. Büyük modal

Büyük modal:

- çok alanlı kısa akışlar için kullanılır
- tam sayfa ihtiyacı doğuyorsa artık modal tercih edilmez

## 4. Modal içinde modal açılmaz

İç içe modal kullanımı yasaktır.

## 5. Uzun akışlar modal içinde çözülmez

Bir işlem çok fazla alan, sekme, karar veya adım gerektiriyorsa ayrı sayfaya alınmalıdır.

# Yasaklar

- modal içinde modal açmak
- uzun formu modal içine gömmek
- birden fazla primary action koymak
- başlıksız modal kullanmak
- gereksiz büyük modal açmak
- modalı tam sayfa form yerine kullanmak

# Uygulama akışı

## Aşama 1

Amaç belirlenir:

- onay mı?
- kısa form mu?
- önizleme mi?

## Aşama 2

Doğru modal boyutu seçilir:

- small
- medium
- large

## Aşama 3

Başlık ve içerik hazırlanır

## Aşama 4

Footer aksiyonları yerleştirilir

## Aşama 5

Kapatma ve iptal davranışı kontrol edilir

# Çıktı formatı

Model şu sırayla cevap vermelidir:

1. modal amacı
2. modal boyutu
3. içerik yapısı
4. footer aksiyonları
5. HTML

# Kabul kriterleri

- modal tek amaçlı olmalı
- doğru boyut seçilmeli
- başlık açık olmalı
- footer düzenli olmalı
- birincil aksiyon net olmalı
- Tabler uyumlu olmalı

# Kirpi notu

Modal, kullanıcıyı hızlandırmalıdır.

Kullanıcı modalı görünce şunu hissetmelidir:

"Bu kısa bir iş, hemen bitiririm."