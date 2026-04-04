---
name: navigation-standardi
description: Kirpi Framework içinde sidebar kullanılmayan yapılarda üst navigasyonun tutarlı, ölçeklenebilir ve kullanıcıyı yönlendiren şekilde tasarlanmasını sağlar.
---

# Amaç

Bu skill, Kirpi Framework içindeki ana navigasyon yapısının tutarlı, sade ve ölçeklenebilir olmasını sağlar.

Amaç; kullanıcıların sistem içinde nerede olduğunu anlamasını, ana modüllere hızlı erişmesini ve üst navigasyonun zamanla kaosa dönüşmesini engellemektir.

# Kapsam

Bu skill aşağıdaki alanları kapsar:

- üst navigasyon (top navigation)
- ana menü öğeleri
- dropdown menüler
- aktif sayfa gösterimi
- kullanıcı menüsü
- bildirim alanı
- tema aksiyonu
- mobil navigasyon davranışı

# Genel prensipler

## 1. Kirpi’de ana navigasyon üst bardadır

Kirpi admin yapısında sidebar kullanılmaz.

Bu nedenle ana navigasyon:

- üst barda yer alır
- sistemin ana modüllerini taşır
- içerik alanından rol çalmaz
- ama yön bulmayı da zayıflatmaz

## 2. Navigation gösteriş alanı değildir

Navigasyon:

- sade olmalıdır
- işlev odaklı olmalıdır
- gereksiz öğelerle şişirilmemelidir

## 3. Her menü öğesi bir amaca hizmet etmelidir

Menüye eklenen her öğe için şu soru sorulmalıdır:

"Bu alan kullanıcı tarafından düzenli kullanılacak mı?"

Cevap hayırsa ana navigasyona alınmamalıdır.

# Zorunlu kurallar

## 1. Ana menü öğeleri sınırlı olmalıdır

Üst navigasyonda aynı anda görünen ana öğe sayısı kontrollü olmalıdır.

Önerilen yaklaşım:

- 4 ila 7 ana öğe idealdir
- daha fazlası dropdown veya grup mantığına alınmalıdır

## 2. Ana menü modül odaklı olmalıdır

Ana navigasyonun temel öğeleri modül bazlı düşünülmelidir.

Örnekler:

- Dashboard
- Kullanıcılar
- Roller
- Ayarlar
- Raporlar

## 3. Menü başlıkları kısa olmalıdır

Navigasyon metinleri:

- kısa
- net
- tek bakışta anlaşılır

olmalıdır.

Yanlış örnekler:

- Kullanıcı ve Yetki Yönetim İşlemleri
- Sistemsel Genel Konfigürasyonlar

Doğru örnekler:

- Kullanıcılar
- Roller
- Ayarlar

## 4. İkonlar Tabler standardında kullanılmalıdır

Navigasyondaki ikonlar:

- Tabler icon sisteminden gelmelidir
- metni desteklemelidir
- metnin önüne geçmemelidir

Custom icon, rastgele SVG veya farklı icon setleri kullanılmaz.

## 5. Aktif sayfa net görünmelidir

Kullanıcının bulunduğu alan görsel olarak açıkça belli olmalıdır.

Aktif state:

- yalnızca renk farkı ile değil
- underline, renk veya vurgu ile belirginleşmelidir

## 6. Dropdown sadece gerektiğinde kullanılmalıdır

Dropdown menü kullanımı ancak şu durumda uygundur:

- aynı bağlamdaki alt sayfalar tek başlık altında toplanıyorsa
- ana menü kalabalığı azaltılıyorsa

Gereksiz dropdown kullanımı yasaktır.

## 7. Sağ alan sabit davranmalıdır

Üst navigasyonun sağ tarafında yalnızca şu tür öğeler yer almalıdır:

- tema aksiyonu
- bildirim
- kullanıcı menüsü

İsteğe bağlı ama kontrollü:

- hızlı aksiyon butonları

Sağ alan, ikinci bir ana menüye dönüştürülmez.

## 8. Kullanıcı menüsü sade olmalıdır

Kullanıcı menüsünde şu tip öğeler bulunabilir:

- Profil
- Tercihler / Hesap
- Çıkış Yap

Kullanıcı menüsü yönetim paneli menüsü gibi davranmamalıdır.

## 9. Mobil davranış zorunludur

Mobilde üst navigasyon:

- collapse olmalıdır
- kullanılabilir kalmalıdır
- yatay sıkışma yaratmamalıdır

Masaüstü davranışı mobilde zorla korunmaz.

## 10. Arama alanı varsayılan olarak zorunlu değildir

Üst barda arama ancak gerçekten ihtiyaç varsa kullanılmalıdır.

Sırf modern görünsün diye arama eklenmez.

# Yasaklar

- sidebar yokken üst navigasyonu düzensiz bırakmak
- gereksiz menü öğeleri eklemek
- uzun menü başlıkları kullanmak
- farklı ikon setleri kullanmak
- her şeyi dropdown içine atmak
- sağ alanı ikinci menü gibi kullanmak
- aktif sayfayı belirsiz bırakmak
- mobil görünümü ihmal etmek

# Navigation yapısı

Kirpi üst navigasyonu üç bölümden oluşur:

## 1. Sol alan
- logo

## 2. Orta alan
- ana modül navigasyonu

## 3. Sağ alan
- tema
- bildirim
- kullanıcı menüsü

# Uygulama akışı

## Aşama 1

Ana modülleri belirle:

- kullanıcı hangi ana alanlara düzenli gider?
- hangi modüller üst navigasyonda görünmeli?

## Aşama 2

Menü yoğunluğunu kontrol et:

- doğrudan gösterilecek öğeler
- dropdown içine alınacak öğeler

## Aşama 3

İkon ve etiketleri belirle:

- kısa başlık
- Tabler icon
- okunabilir spacing

## Aşama 4

Aktif state davranışını tanımla

## Aşama 5

Mobil collapse akışını kontrol et

# Çıktı formatı

Model şu sırayla cevap vermelidir:

1. navigasyon amacı
2. ana menü öğeleri
3. dropdown yapısı
4. sağ alan öğeleri
5. mobil davranış
6. kod

# Kabul kriterleri

- navigation sade olmalı
- menü başlıkları kısa olmalı
- Tabler icon sistemi kullanılmalı
- aktif state açık olmalı
- mobil davranış düşünülmüş olmalı
- sağ alan kontrolsüz büyümemeli

# Kirpi notu

Kirpi’de navigation yalnızca menü değildir.

Navigation, kullanıcının sistem içindeki yön duygusudur.

Her düzenlemede şu soru sorulmalıdır:

"Kullanıcı bu yapıya ilk kez baktığında nereye gideceğini anlayabiliyor mu?"