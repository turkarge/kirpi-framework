---
name: crud-standardi
description: Kirpi Framework içinde CRUD geliştirirken modüler mimariyi, katman ayrımını, liste-form akışını, validation disiplinini ve UI standardını korumayı sağlar.
---

# Amaç

Bu skill, Kirpi Framework içinde geliştirilen CRUD yapılarının tutarlı, sürdürülebilir ve framework karakterine uygun şekilde üretilmesini sağlar.

Amaç; her CRUD geliştirmesinin aynı kalite seviyesinde, aynı akış mantığıyla ve aynı mimari disiplinle ilerlemesini garanti altına almaktır.

# Ne zaman kullanılır?

Bu skill aşağıdaki durumlarda kullanılmalıdır:

- Yeni bir CRUD ekranı geliştirirken
- Mevcut bir CRUD yapısını genişletirken
- Listeleme, ekleme, düzenleme, silme akışlarını kurarken
- Modül içindeki veri yönetimi ekranlarını oluştururken
- Generator ile üretilen iskeleti tamamlayıp gerçek iş mantığına geçirirken

# Kirpi bağlamı

Kirpi Framework içinde CRUD geliştirme tek başına bir ekran üretme işi değildir.

CRUD; modül yapısı, route standardı, validation, UI davranışı, geri bildirimler ve gerekiyorsa manager/developer test akışı ile birlikte ele alınmalıdır.

Framework içinde generator yaklaşımı (`make:module`, `make:crud`) ve manager panelindeki module wizard bu süreci destekleyen temel araçlardır.

# Zorunlu kurallar

## 1. CRUD modül mantığı içinde yaşamalıdır
- CRUD yapısı ilgili modül içinde konumlandırılmalıdır.
- Core alanı CRUD’e özel uygulama mantığı ile kirletilmemelidir.
- Manager yüzeyi ile uygulama CRUD yüzeyi birbirine karıştırılmamalıdır.

## 2. Katman ayrımı korunmalıdır
CRUD geliştirmesi mümkün olduğunca şu ayrımı izlemelidir:

- Controller: istek alır, akışı yönlendirir
- Service: iş mantığını yürütür
- Request / Validation: veri doğrular
- Repository veya veri erişim katmanı: veri işlemlerini yürütür
- View: sunumu yapar

Controller içinde yoğun iş mantığı tutulmamalıdır.

## 3. Listeleme tamamlanmadan CRUD tamam sayılmaz
Bir CRUD yapısı sadece create/update ile tamamlanmış kabul edilmez.

Liste ekranında aşağıdakiler düşünülmelidir:
- anlamlı kolonlar
- uygun sıralama
- filtre ihtiyacı
- sayfalama ihtiyacı
- boş durum görünümü
- işlem butonları
- geri bildirimler

## 4. Form akışı zorunlu olarak ele alınmalıdır
Form ekranlarında aşağıdakiler düşünülmelidir:
- zorunlu alanlar
- varsayılan değerler
- doğrulama kuralları
- hata mesajları
- düzenleme modunda veri doldurma
- başarı sonrası yönlendirme veya geri bildirim

## 5. Validation atlanamaz
- Veri doğrulama controller içinde dağınık biçimde yazılmamalıdır.
- Validation görünmez bir yan detay gibi ele alınmamalıdır.
- Create ve update senaryoları gerektiğinde ayrı düşünülmelidir.

## 6. Silme davranışı açık tanımlanmalıdır
- Silme hard delete mi soft delete mi net olmalıdır.
- Silme işlemi geri döndürülebilir mi, değil mi açıkça düşünülmelidir.
- Kritik kayıtlar için doğrudan fiziksel silme rastgele uygulanmamalıdır.

## 7. UI Kirpi standardına uymalıdır
- Tabler yaklaşımı korunmalıdır.
- Responsive davranış bozulmamalıdır.
- Dark mode / light mode uyumu korunmalıdır.
- Bildirimler toastr standardına uygun olmalıdır.
- Başarı, hata, boş durum ve loading davranışları düşünülmeden ekran tamamlanmış sayılmaz.

## 8. Generator çıktısı başlangıçtır, sonuç değildir
- `make:crud` ile gelen yapı nihai çözüm gibi kabul edilmemelidir.
- Generator iskeleti, gerçek domain ihtiyacına göre tamamlanmalıdır.
- Üretilen iskelet mevcut modül ve UI davranışlarıyla uyumlu hale getirilmelidir.

# Yasaklar

Aşağıdakiler yasaktır:

- Controller içine validation ve iş mantığını doldurmak
- Sadece create/edit ekranı yapıp listeyi ikinci plana atmak
- Filtre, sayfalama veya boş durumları hiç düşünmeden CRUD tamam demek
- Başarı ve hata bildirimlerini ihmal etmek
- UI tarafında Tabler standardını bozmak
- Dark/light mode uyumunu hesaba katmamak
- Validation kurallarını dağınık ve tekrar eden şekilde gömmek
- Silme davranışını belirsiz bırakmak
- Manager endpoint mantığı ile uygulama CRUD route’larını karıştırmak
- Sadece generator çıktısını bırakıp işi bitmiş saymak

# Uygulama akışı

## Aşama 1: CRUD kapsamını tanımla
- Hangi varlık yönetiliyor?
- Bu kayıt hangi modüle ait?
- Alanlar neler?
- İlişkiler var mı?
- Soft delete gerekiyor mu?

## Aşama 2: Etki analizi yap
- Hangi route'lar gerekecek?
- Hangi controller/service/request/view dosyaları gerekecek?
- Liste, form, detay veya silme ekranı/aksiyonu olacak mı?
- UI tarafında test/demo ihtiyacı var mı?

## Aşama 3: Akışı planla
En az şu akış değerlendirilmelidir:
1. Liste
2. Oluştur
3. Kaydet
4. Düzenle
5. Güncelle
6. Sil
7. Geri bildirim ve yönlendirme

## Aşama 4: Kod üret
- Önce generator veya mevcut yapı ile iskelet oluştur
- Sonra validation kur
- Sonra service ve veri akışını tamamla
- Ardından UI ekranlarını Kirpi standardına göre oturt
- Geri bildirimleri ekle

## Aşama 5: Doğrula
- Liste düzgün çalışıyor mu?
- Form create/edit modlarında doğru davranıyor mu?
- Validation mesajları doğru mu?
- Başarı/hata toastr bildirimleri çalışıyor mu?
- Responsive görünüm korunuyor mu?
- Dark/light mode bozuluyor mu?
- Silme davranışı net mi?

# Çıktı formatı

Model şu sırayla cevap vermelidir:

1. CRUD konusu ve amacı
2. Etkilenen modül ve dosyalar
3. Veri alanları ve akış özeti
4. Uygulama planı
5. Kod değişiklikleri
6. Doğrulama özeti

Doğrudan çok dosyalı kodla başlanmamalı; önce kısa plan verilmelidir.

# Kabul kriterleri

Bu skill’e uygun bir CRUD geliştirmesi şu şartları sağlamalıdır:

- İlgili modül içinde konumlanmış olmalı
- Katman ayrımı korunmuş olmalı
- Liste, form ve silme akışı net olmalı
- Validation görünür ve sürdürülebilir biçimde tanımlanmış olmalı
- UI Kirpi standardına uygun olmalı
- Toastr geri bildirimleri düşünülmüş olmalı
- Responsive ve dark/light uyumu korunmuş olmalı
- Generator çıktısı gerçek ihtiyaca göre tamamlanmış olmalı

# Kirpi'ye özel not

Kirpi içinde CRUD geliştirmek yalnızca veri girişi ekranı yapmak değildir.

Amaç; framework içinde yaşayan, modül mantığına uyan, yönetilebilir ve tekrar üretilebilir bir veri yönetim akışı kurmaktır.

Her CRUD için şu soru sorulmalıdır:

"Bu yalnızca çalışan bir ekran mı, yoksa Kirpi standardına uyan gerçek bir yönetim akışı mı?"