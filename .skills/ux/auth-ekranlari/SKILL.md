---
name: auth-ekranlari
description: Kirpi Framework içinde login, şifre sıfırlama ve kilit ekranlarının tutarlı, sade ve sistem ayarlarına bağlı şekilde geliştirilmesini sağlar.
---

# Amaç

Bu skill, Kirpi Framework içindeki tüm authentication ekranlarının (login, forgot password, lock screen) aynı UX diline sahip olmasını sağlar.

Amaç; kullanıcıyı yormayan, hızlı, sade ve sistemle entegre çalışan bir giriş deneyimi oluşturmaktır.

# Kapsam

Bu skill aşağıdaki ekranları kapsar:

- Login ekranı
- Şifre sıfırlama (forgot password)
- Kilit ekranı (lock screen)

# Genel prensipler

## 1. Login ekranı farklıdır

Login ekranı:

- marka gösterir
- cover içerir
- görsel olarak daha zengindir

Forgot ve lock screen:

- sade
- hızlı
- odaklı

## 2. Minimal dikkat dağıtma

Auth ekranlarında aşağıdakiler kullanılmaz:

- gereksiz kartlar
- uzun açıklamalar
- pazarlama içerikleri

## 3. Tek amaç prensibi

Her ekran tek bir işi yapmalıdır:

- Login → giriş
- Forgot → reset talebi
- Lock → oturuma devam

# Zorunlu kurallar

## 1. Logo dinamik olmalıdır

Logo sabit yazılamaz.

Logo kaynağı:

- sistem ayarları
- tenant ayarları (varsa)

Hardcoded logo kullanımı yasaktır.

## 2. Tema sistemden gelmelidir

Dark / light mode UI içinde belirlenmez.

- sistem ayarına göre belirlenir
- kullanıcı tercihi ile değişebilir

UI sadece bu ayara uyum sağlar.

## 3. Layout standardı korunmalıdır

### Login ekranı

- iki kolon
- sol: form
- sağ: cover

### Forgot ve Lock ekranları

- tek kolon
- ortalanmış form

## 4. Form genişliği sabittir

- maksimum genişlik: 360px – 420px
- ekran ortalanmış olmalıdır

## 5. Input standardı

- label zorunludur
- placeholder destekleyicidir
- validation alanı düşünülmelidir

## 6. CTA standardı

- tek primary buton
- full width
- açık ve net metin

Örnekler:

- Giriş Yap
- Bağlantı Gönder
- Devam Et

## 7. State yönetimi zorunludur

Her auth ekranı şu durumları desteklemelidir:

- loading
- success
- error

## 8. Sosyal login default olarak yoktur

- Google / GitHub login default kapalıdır
- opsiyonel olarak açılabilir

## 9. Footer minimal olmalıdır

Sadece aşağıdaki düzeyde minimal bir yapı kullanılmalıdır:

`© Kirpi Framework`

## 10. Mobil davranış

- login ekranında cover gizlenir
- form merkezde kalır
- buton full width olur

# Yasaklar

- hardcoded logo kullanımı
- sabit tema rengi zorlamak
- gereksiz açıklama metni
- çoklu CTA kullanımı
- pazarlama dili
- sosyal login’in default açık olması
- dikkat dağıtıcı içerik

# Uygulama akışı

## Aşama 1

Ekran türünü belirle:

- login
- forgot
- lock

## Aşama 2

Layout seç:

- login → split
- diğerleri → center

## Aşama 3

Logo ve tema entegrasyonu ekle.

## Aşama 4

Form ve CTA yerleştir.

## Aşama 5

State davranışlarını ekle.

## Aşama 6

Mobil kontrol yap.

# Çıktı formatı

Model şu sırayla cevap vermelidir:

1. Ekran türü
2. Layout açıklaması
3. Kullanılan bileşenler
4. State yönetimi
5. Kod

# Kabul kriterleri

- logo dinamik olmalı
- tema uyumlu olmalı
- layout standarda uygun olmalı
- tek amaç prensibi korunmalı
- mobil uyum sağlanmalı

# Kirpi notu

Auth ekranları bir tasarım değil, bir deneyimdir.

Her ekran için şu soru sorulmalıdır:

"Kullanıcı bu ekranda en hızlı şekilde amacına ulaşabiliyor mu?"