---
name: kirpi-ux-prensipleri
description: Kirpi Framework içindeki tüm kullanıcı arayüzlerinin ortak tasarım dili, bileşen standardı ve davranış kurallarını tanımlar.
---

# Amaç

Bu skill, Kirpi Framework içinde geliştirilen tüm UI/UX yapıların aynı tasarım diline ve davranış standardına sahip olmasını sağlar.

Amaç; rastgele ekran üretimini engellemek, tekrar kullanılabilir ve sürdürülebilir bir arayüz sistemi oluşturmaktır.

---

# Temel yaklaşım

Kirpi’de UI geliştirme:

- ekran üretmek değildir
- bileşenleri rastgele birleştirmek değildir

Kirpi’de UI geliştirme:

> standart bir sistem içinde ekran oluşturmaktır

---

# kirpi-ui-kit referansı

Kirpi UI geliştirme sürecinde temel referans:

👉 kirpi-ui-kit

Bu kit aşağıdaki yapıların temelini oluşturur:

- layout yapıları
- form bileşenleri
- tablo yapıları
- kart sistemleri
- spacing kuralları
- tipografi
- aksiyon alanları

Yeni geliştirilen her ekran bu referansa uygun olmalıdır.

---

# Zorunlu prensipler

## 1. Tutarlılık zorunludur

Aynı bileşen:

- farklı sayfalarda farklı davranamaz
- farklı spacing kullanamaz
- farklı renk mantığıyla çalışamaz

---

## 2. Tekrar kullanılabilirlik esastır

Her UI parçası:

- yeniden kullanılabilir olmalıdır
- modül bağımsız düşünülmelidir

---

## 3. Tasarım değil sistem önceliklidir

Yeni bir ekran geliştirilirken:

❌ “nasıl daha güzel yaparım”  
✔ “sisteme nasıl uyarlarım”

---

## 4. Minimalizm

Kirpi UI:

- sade
- net
- dikkat dağıtmayan

olmalıdır.

---

## 5. Tek amaç prensibi

Her ekranın:

- tek bir ana amacı olmalıdır
- kullanıcıyı kararsız bırakmamalıdır

---

## 6. Görsel hiyerarşi

- başlık → içerik → aksiyon sırası korunmalıdır
- primary ve secondary aksiyonlar net ayrılmalıdır

---

## 7. Feedback zorunludur

Kullanıcı yaptığı işlemin sonucunu mutlaka görmelidir:

- başarı
- hata
- uyarı
- loading

---

## 8. Tema uyumu

- dark / light mode desteklenmelidir
- UI sabit renk dayatmamalıdır

---

## 9. Mobil uyum zorunludur

- responsive sadece “çalışıyor” seviyesinde değil
- kullanılabilir olmalıdır

---

## 10. Hardcoded veri kullanılmaz

- logo
- renk
- metin

mümkün olduğunca sistemden gelmelidir

---

# Yasaklar

- rastgele UI üretmek
- kirpi-ui-kit dışında component üretmek
- farklı sayfalarda farklı davranan butonlar
- aynı iş için farklı layout kullanmak
- gereksiz görsel karmaşa
- kullanıcıyı yönlendirmeyen ekranlar

---

# UI katmanları

Kirpi UI 3 katmandan oluşur:

## 1. Layout katmanı

- auth layout
- admin layout

## 2. Sayfa katmanı

- login
- liste
- form
- dashboard
- ayarlar

## 3. Bileşen katmanı

- input
- button
- table
- card
- alert
- modal

---

# Geliştirme yaklaşımı

Yeni bir ekran geliştirirken:

1. ekran türünü belirle
2. uygun layout seç
3. kirpi-ui-kit bileşenlerini kullan
4. yeni component üretmekten kaçın
5. state davranışlarını ekle
6. mobil uyumu kontrol et

---

# Çıktı formatı

Model şu sırayla cevap vermelidir:

1. ekran amacı
2. kullanılan layout
3. bileşen listesi
4. UX kararları
5. kod

---

# Kabul kriterleri

Bir UI geliştirmesi şu şartları sağlamalıdır:

- kirpi-ui-kit ile uyumlu olmalı
- tutarlı olmalı
- tekrar kullanılabilir olmalı
- sade olmalı
- kullanıcıyı yönlendirmeli
- mobil uyumlu olmalı

---

---

# Referans fallback kuralı

Kirpi UX geliştirme sürecinde bir bileşen, layout veya davranış ile ilgili yeterli tanım bulunamıyorsa aşağıdaki referans sırası uygulanır:

1. kirpi-ui-kit
2. ilgili UX skill (auth, form, liste vb.)
3. Tabler referansı

Tabler referansı:

https://github.com/tabler/tabler

---

## Tabler kullanım kuralı

Tabler birincil kaynak değildir.

Sadece aşağıdaki durumlarda kullanılır:

- Kirpi UI Kit içinde karşılığı yoksa
- Skill içinde tanım yoksa
- Yeni bir bileşen gerekiyorsa

---

## Tabler kullanılırken dikkat edilmesi gerekenler

- birebir kopyalama yapılmaz
- Kirpi tasarım diline uyarlanır
- gereksiz bileşenler alınmaz
- minimal ve sade kullanım tercih edilir

---

## Yasak

- Tabler bileşenlerini doğrudan kullanmak
- Kirpi standardını bozacak şekilde entegre etmek
- gereksiz UI karmaşası oluşturmak

---

## Amaç

Tabler kullanımı:

> boşlukları doldurmak içindir, sistemi yönlendirmek için değil

# Kirpi notu

Kirpi’de UI geliştirmek tasarım yapmak değildir.

Amaç:

> tekrar üretilebilir, standart ve öngörülebilir bir arayüz sistemi kurmaktır.

Her ekran için şu soru sorulmalıdır:

"Bu ekran Kirpi sistemine uyuyor mu, yoksa sadece çalışıyor mu?"