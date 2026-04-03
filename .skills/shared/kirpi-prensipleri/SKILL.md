---
name: kirpi-prensipleri
description: Kirpi Framework üzerinde geliştirme yaparken mimari bütünlüğü, güvenlik yaklaşımını, UI standartlarını ve üretim disiplinini korumak için temel kuralları tanımlar.
---

# Amaç

Bu skill, Kirpi Framework içinde yapılan tüm geliştirmelerde ortak çalışma zihniyetini sabitler.

Amaç; yeni kod yazarken mevcut mimariyi bozmayı, rastgele yapı kurmayı, UI davranışlarını dağıtmayı ve güvenlik/operasyon standartlarını ihmal etmeyi önlemektir.

Bu skill, diğer tüm Kirpi skill'lerinin üst kural setidir. Başka bir skill kullanılsa bile bu skill geçerliliğini korur.

# Ne zaman kullanılır?

Bu skill aşağıdaki tüm durumlarda uygulanmalıdır:

- Yeni modül geliştirirken
- CRUD yapısı kurarken
- Route eklerken veya değiştirirken
- Manager yüzeyine yeni özellik eklerken
- UI bileşeni, sayfa, test page veya etkileşim geliştirirken
- Refactor yaparken
- Hata düzeltirken
- Yeni servis, controller, middleware veya view eklerken

# Kirpi'nin temel yaklaşımı

Kirpi Framework şu ilkelere göre değerlendirilmelidir:

1. Mevcut yapıyı koru, keyfi yeni yapı icat etme.
2. Kod; Core, Modules ve Manager sorumluluk ayrımına saygı duymalıdır.
3. Uygulama yüzeyi ile operasyon/manager yüzeyi birbirine karıştırılmamalıdır.
4. Güvenlik, sonradan eklenecek bir detay değil; başlangıç kuralıdır.
5. UI tarafında mevcut tema ve davranış standartları korunmalıdır.
6. Geliştirme akışı doğrudan kod üretimi ile başlamamalı; önce analiz ve plan yapılmalıdır.
7. Var olan dokümantasyon, komutlar ve standartlar yok sayılmamalıdır.
8. Deploy hedefi Dokploy uyumunu bozacak keyfi kararlar alınmamalıdır.

# Zorunlu kurallar

## 1. Mimari bütünlük
- Core, framework seviyesindeki ortak altyapıyı temsil eder.
- Modules, işlevsel uygulama modüllerini temsil eder.
- Manager, operasyonel yönetim ve kontrol düzlemidir.
- Bu üç alanın sorumlulukları birbirine taşınmamalıdır.

## 2. Mevcut standarda uyum
- Yeni geliştirme, mevcut klasör yapısına ve adlandırma yaklaşımına uymalıdır.
- Framework içinde zaten bulunan yaklaşım varken paralel ikinci bir sistem kurulmaz.
- "Daha kolay geldi" gerekçesiyle mevcut mimari bypass edilmez.

## 3. Analizden önce kod yok
Her görev şu sırayla ilerlemelidir:
1. İhtiyacı anla
2. Etkilenen dosyaları belirle
3. Mevcut yapıya etkisini değerlendir
4. Plan çıkar
5. Sonra kod üret
6. Son olarak doğrulama yap

## 4. Güvenlik varsayılan olarak zorunludur
- Özellikle manager yüzeyine eklenen her yetenek hassas kabul edilmelidir.
- Auth, token, throttle, whitelist, audit veya benzeri güvenlik katmanları göz ardı edilmemelidir.
- İç kullanım bahanesiyle güvenlik gevşetilmemelidir.

## 5. UI standardına bağlılık
- Tabler tema yaklaşımı korunmalıdır.
- Responsive davranış zorunludur.
- Dark mode / light mode uyumu bozulmamalıdır.
- Bildirim standardı toastr yaklaşımına uygun olmalıdır.
- UI geliştirmeleri geri bildirim, hata durumu, boş durum ve loading davranışlarını düşünmeden tamamlanmış sayılmaz.

## 6. Operasyon ve deploy duyarlılığı
- Yapılan değişiklikler Docker/Dokploy uyumunu bozacak şekilde tasarlanmamalıdır.
- Servis, worker, nginx, manager ve benzeri operasyonel yüzeyler hesapsız değiştirilmemelidir.
- Konfigürasyon ve environment bağımlılıkları dikkatle ele alınmalıdır.

## 7. Dokümantasyon saygısı
- Mevcut README ve docs yapısı yok sayılmamalıdır.
- Gerekli durumlarda yeni davranış için dokümantasyon güncellenmelidir.
- Yeni özellik eklenip hiçbir açıklama bırakılmaması kabul edilmez.

# Yasaklar

Aşağıdakiler yasaktır:

- Controller içine yoğun iş mantığı gömmek
- Mevcut route yaklaşımını kıracak rastgele isimlendirme yapmak
- Modules ve Manager sınırlarını ihlal etmek
- UI tarafında Tabler dışında keyfi desenler kullanmak
- Responsive, dark/light mode ve bildirim standardını bozmak
- Güvenlik katmanlarını test kolaylığı adına devre dışı bırakmak
- Mevcut scaffolding ve üretim mantığını yok sayıp ayrı mini framework davranışları üretmek
- Analiz yapmadan doğrudan çok dosyalı büyük kod üretmek
- Mevcut davranışı etkileyecek değişiklikleri doğrulama yapmadan tamamlanmış kabul etmek

# Uygulama akışı

Kirpi üzerinde görev çalışırken aşağıdaki çıktı düzeni izlenmelidir:

## Aşama 1: Durumu anla
- Görev nedir?
- Hangi alanı etkiler?
- Core, Modules veya Manager'dan hangisine aittir?

## Aşama 2: Etki analizi yap
- Hangi dosyalar etkilenecek?
- Yeni dosya mı açılacak?
- Mevcut bir standardı etkiliyor mu?
- Güvenlik veya operasyonel etkisi var mı?

## Aşama 3: Uygulama planı çıkar
- Hangi sırayla değişiklik yapılacak?
- Hangi katmanlar değişecek?
- Hangi kısımlar özellikle korunacak?

## Aşama 4: Kod üret
- Sadece planla ilişkili değişiklikleri yap
- Gereksiz refactor veya konu dışı temizlik yapma
- Mevcut stil ve yaklaşımı koru

## Aşama 5: Doğrula
- Route ve akış doğru mu?
- UI standardı bozuldu mu?
- Manager güvenliği etkilendi mi?
- Responsive/dark-light/toastr davranışı korundu mu?
- Gerekirse test page, docs veya not eklendi mi?

# Çıktı formatı

Kirpi üzerinde görev yapan model şu formatta ilerlemelidir:

1. Görevin kısa özeti
2. Etkilenen alanlar ve dosyalar
3. Riskler veya dikkat edilmesi gereken noktalar
4. Uygulama planı
5. Kod değişiklikleri
6. Doğrulama özeti

Doğrudan kodla başlamak yerine önce kısa ve net bir plan sunulmalıdır.

# Kabul kriterleri

Bu skill'e uygun bir çalışma şu şartları sağlamalıdır:

- Kirpi'nin mevcut mimari ayrımı korunmuş olmalı
- Kod mevcut yapı ve adlandırma standardına uyumlu olmalı
- Güvenlik hassasiyetleri göz ardı edilmemiş olmalı
- UI tarafında belirlenen standartlar korunmuş olmalı
- Dokploy/deploy yaklaşımını bozacak riskli keyfi değişiklik yapılmamış olmalı
- Gereksiz kapsam kayması olmamalı
- Sonuç açıklanabilir ve doğrulanabilir olmalı

# Kirpi'ye özel not

Kirpi üzerinde geliştirirken amaç sadece çalışan kod üretmek değildir.

Amaç; güvenli, standart, yönetilebilir, tekrar edilebilir ve framework karakterini koruyan üretim yapmaktır.

Bu nedenle her yeni geliştirme, "çalışıyor mu?" sorusundan önce "Kirpi gibi davranıyor mu?" sorusunu geçmelidir.
