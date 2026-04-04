---
name: form-ekrani
description: Kirpi Framework içinde form ekranlarının sade, hataya dayanıklı ve kullanıcıyı yönlendiren şekilde oluşturulmasını sağlar.
---

# Amaç

Form ekranı, kullanıcıdan veri alınan en kritik alandır.

Amaç:

- doğru veri almak
- kullanıcıyı yormamak
- hatayı minimize etmek
- işlemi hızlı tamamlatmak

# Genel prensipler

## 1. Form sade olmalıdır

Form:

- gereksiz alan içermemelidir
- okunabilir olmalıdır
- bölünmemelidir (gereksiz tab/accordion yok)

## 2. Form hata yaptırmamalıdır

- doğru input tipi kullanılmalıdır
- placeholder yardımcı olmalıdır
- alan isimleri açık olmalıdır

## 3. Aksiyonlar net olmalıdır

Her formda:

- Kaydet (primary)
- İptal (secondary)

olmalıdır

# Zorunlu bileşenler

## 1. Page Header

- başlık (Yeni / Düzenle)
- kısa açıklama

## 2. Form Container

Form:

- card içinde olmalıdır
- padding standardına uymalıdır

## 3. Input Alanları

Alanlar:

- label içermelidir
- doğru input tipi kullanılmalıdır
- grid sistemi ile hizalanmalıdır

## 4. Footer

Form sonunda:

- sağ hizalı aksiyonlar
- iptal + kaydet

# Görsel kurallar

## 1. Grid sistemi

- 2 kolon (md-6) standarttır
- gerekli durumlarda tek kolon

## 2. Alan gruplama

- ilişkili alanlar yan yana
- ilgisiz alanlar ayrı

## 3. Buton hiyerarşisi

- primary: Kaydet
- secondary: İptal

# Davranış kuralları

## 1. Validation düşünülmelidir

- boş alanlar kontrol edilir
- e-posta formatı kontrol edilir
- şifre eşleşmesi kontrol edilir

## 2. Hata mesajları açık olmalıdır

Kullanıcıya:

- neyin yanlış olduğu
- nasıl düzeltileceği

net şekilde söylenmelidir

## 3. Form akışı hızlı olmalıdır

- gereksiz alan yok
- gereksiz adım yok

# Yasaklar

- uzun ve bölünmüş formlar
- gereksiz alanlar
- label’sız input
- sadece placeholder kullanımı
- kaydet butonunu gizlemek
- birden fazla primary action

# Uygulama akışı

1. form amacı belirlenir
2. gerekli alanlar seçilir
3. layout planlanır
4. input tipleri belirlenir
5. validation kuralları eklenir
6. aksiyonlar eklenir

# Çıktı formatı

1. form amacı
2. alan listesi
3. layout planı
4. validation
5. aksiyonlar
6. HTML

# Kabul kriterleri

- sade olmalı
- hataya açık olmamalı
- Tabler uyumlu olmalı
- aksiyonlar net olmalı

# Kirpi notu

Form ekranı, kullanıcının sabrını test eder.

İyi form:

kullanıcıyı düşündürmez.

Kötü form:

kullanıcıyı sistemden soğutur.