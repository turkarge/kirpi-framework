---
name: test-page-ve-ui-standardi
description: Kirpi Framework içinde UI ekranları, demo/test page’ler ve kullanıcı etkileşimlerinin Tabler tabanlı, tutarlı, erişilebilir ve doğrulanabilir şekilde geliştirilmesini sağlar.
---

# Amaç

Bu skill, Kirpi Framework içinde geliştirilen tüm UI yüzeylerinin aynı davranış diline sahip olmasını sağlar.

Amaç; yeni sayfa, bileşen, form, liste, demo veya test page geliştirirken görsel tutarsızlığı, akış dağınıklığını ve kullanıcı geri bildirimi eksikliğini önlemektir.

# Ne zaman kullanılır?

Bu skill aşağıdaki durumlarda kullanılmalıdır:

- Yeni bir yönetim ekranı geliştirirken
- Yeni bir modül için liste veya form sayfası hazırlarken
- Yeni UI bileşeni veya etkileşim eklerken
- Notify, modal, state, import/export, PWA, a11y veya benzeri frontend kabiliyetleri denerken
- Demo veya test page hazırlarken
- Mevcut bir ekranı refactor ederken

# Kirpi bağlamı

Kirpi Framework içinde frontend rastgele şekillenmez.

Aşağıdaki kararlar temel kabul edilir:
- Tabler tema sistemi kullanılır
- Responsive davranış zorunludur
- Dark mode / light mode uyumu korunur
- Bildirim sistemi toastr yaklaşımıyla ilerler
- Yeni frontend davranışları mümkünse test page ile görünür ve doğrulanabilir hale getirilir

# Zorunlu kurallar

## 1. Tabler standardı korunmalıdır
- Yeni ekranlar mevcut Tabler yapısına uyumlu olmalıdır
- Kart, grid, form, tablo, toolbar ve buton kullanımı mevcut tema diliyle örtüşmelidir
- Keyfi üçüncü desenler veya görsel karmaşa üretilmemelidir

## 2. Responsive davranış zorunludur
- Masaüstünde çalışan ama mobilde dağılan ekran tamamlanmış sayılmaz
- Tablo, form, aksiyon alanı ve buton yerleşimleri küçük ekranlarda da düşünülmelidir
- Yatay taşma ve kırık hizalama kabul edilmez

## 3. Dark mode / light mode uyumu korunmalıdır
- Sadece tek temada düzgün görünen ekran kabul edilmez
- Renk, kontrast, ikon ve geri bildirim öğeleri iki modda da okunabilir olmalıdır
- Sabit açık renk arka plan veya sabit koyu yazı gibi kırıcı kararlar alınmamalıdır

## 4. Bildirim ve geri bildirim zorunludur
- Başarı, hata, uyarı ve bilgi durumları görünür geri bildirim üretmelidir
- Toastr standardı korunmalıdır
- Kullanıcı bir işlem yaptıysa sonucu belirsiz bırakılmamalıdır

## 5. Empty state ve loading state düşünülmelidir
- Veri yoksa ekran boş bırakılmamalıdır
- Yükleme devam ederken kullanıcı karanlıkta bırakılmamalıdır
- İlk yüklenme, boş sonuç ve hata durumu ayrı ayrı ele alınmalıdır

## 6. Form deneyimi tutarlı olmalıdır
- Zorunlu alanlar net anlaşılmalıdır
- Hata mesajları alanla ilişkili gösterilmelidir
- Buton hiyerarşisi açık olmalıdır
- Kaydet, iptal, geri dön gibi aksiyonlar tutarlı konumlanmalıdır

## 7. Liste ekranı yalnızca tablo değildir
- Liste sayfası başlık, açıklama, filtre, aksiyonlar ve sonuç alanı ile birlikte düşünülmelidir
- Birincil aksiyon net olmalıdır
- Satır aksiyonları anlaşılır olmalıdır
- Toplu işlem varsa açık biçimde gösterilmelidir

## 8. Test page yaklaşımı kullanılmalıdır
- Yeni bir frontend davranışı veya bileşen ekleniyorsa mümkünse test page hazırlanmalıdır
- Test page, özelliğin sadece varlığını değil davranışını da gösterebilmelidir
- Demo ekranı geliştiriciye doğrulama kolaylığı sağlamalıdır

## 9. Erişilebilirlik göz ardı edilmemelidir
- Etiket, odak, buton anlamı ve temel klavye kullanılabilirliği düşünülmelidir
- Yalnızca renkle anlam taşıyan kritik bildirimler tercih edilmemelidir
- Tıklanabilir alanlar belirsiz bırakılmamalıdır

# Yasaklar

Aşağıdakiler yasaktır:

- Tabler dilini bozacak keyfi UI kurmak
- Mobil görünümü hiç düşünmeden ekranı tamam saymak
- Dark mode / light mode uyumunu ihmal etmek
- İşlem sonucu için kullanıcıya hiçbir geri bildirim vermemek
- Veri yokken bomboş ekran bırakmak
- Yüklenme durumunu belirsiz bırakmak
- Form hata mesajlarını görünmez veya ilişkisiz göstermek
- Aksiyon butonlarını rastgele yerleştirmek
- Test page gerektiren bir özelliği sadece teorik bırakmak

# Uygulama akışı

## Aşama 1: Ekran amacını tanımla
- Bu ekran neyi çözüyor?
- Kullanıcının birincil hedefi ne?

## Aşama 2: Durumları belirle
- İlk yüklenme
- Başarılı veri gösterimi
- Boş durum
- Hata durumu
- Başarı/uyarı bildirimleri

## Aşama 3: Yerleşimi planla
- Başlık
- Birincil aksiyon
- Filtreler
- İçerik alanı
- Satır/öğe aksiyonları
- Geri bildirim alanları

## Aşama 4: Temaya uygun uygula
- Tabler bileşen diliyle geliştir
- Responsive davranışı kontrol et
- Dark/light görünümü bozma
- Toastr geri bildirimlerini ekle

## Aşama 5: Test page veya doğrulama yüzeyi hazırla
- Özelliği görünür kıl
- Temel akışları test edilebilir hale getir

## Aşama 6: Doğrula
- Mobilde düzgün mü?
- Dark/light modda okunuyor mu?
- Empty/loading/hata durumları ele alındı mı?
- Toastr veya eşdeğer geri bildirimler doğru mu?
- Birincil aksiyon ilk bakışta anlaşılıyor mu?

# Çıktı formatı

Model şu sırayla cevap vermelidir:

1. Ekranın amacı
2. Kullanıcı akışı özeti
3. Gerekli UI durumları
4. Yerleşim ve bileşen planı
5. Geri bildirim ve etkileşim notları
6. Kod veya şablon önerisi
7. Doğrulama özeti

Doğrudan HTML veya view dökmek yerine önce ekran davranışı netleştirilmelidir.

# Kabul kriterleri

Bu skill'e uygun bir UI geliştirmesi şu şartları sağlamalıdır:

- Tabler standardına uyumlu olmalı
- Responsive çalışmalı
- Dark/light mode uyumu korunmalı
- Toastr tabanlı geri bildirim düşünülmüş olmalı
- Empty, loading ve hata durumları ele alınmış olmalı
- Form veya liste akışı anlaşılır olmalı
- Gerekliyse test page yaklaşımı uygulanmış olmalı

# Kirpi'ye özel not

Kirpi içinde UI geliştirmek sadece güzel görünen ekran yapmak değildir.

Amaç; test edilebilir, tekrar üretilebilir, kullanıcıyı yönlendiren ve framework’ün karakterini bozmayan ekranlar üretmektir.

Her ekran için şu soru sorulmalıdır:

"Bu ekran yalnızca çalışıyor mu, yoksa Kirpi’nin davranış dilini gerçekten taşıyor mu?"