---
name: manager-guvenlik
description: Kirpi Framework içinde manager yüzeyine eklenen route, controller, işlem ve araçların güvenli, sınırlı ve denetlenebilir şekilde geliştirilmesini sağlar.
---

# Amaç

Bu skill, Kirpi Framework içindeki manager yüzeyinin güvenlik disiplinini korur.

Amaç; manager paneli, manager API'leri ve operasyonel araçlar geliştirilirken yetkisiz erişim, aşırı yetki, yanlış endpoint tasarımı, zayıf doğrulama ve kontrolsüz işlem risklerini önlemektir.

# Ne zaman kullanılır?

Bu skill aşağıdaki durumlarda kullanılmalıdır:

- Yeni bir manager route eklenirken
- Manager paneline yeni sayfa veya araç eklenirken
- Manager API endpoint'i geliştirilirken
- Sistem ayarı, bakım, yedekleme, test, izleme veya operasyon aracı eklenirken
- Modül keşfi, üretim wizard'ı, runtime kontrolü veya benzeri yetkili işlemler geliştirilirken
- Mevcut manager davranışı refactor edilirken

# Manager yüzeyinin doğası

Manager alanı sıradan bir uygulama ekranı değildir.

Buradaki işlemler:
- sistem davranışını etkileyebilir
- veri bütünlüğünü bozabilir
- operasyonel yan etki oluşturabilir
- güvenlik açığına dönüşebilir

Bu nedenle manager tarafındaki her geliştirme varsayılan olarak hassas kabul edilmelidir.

# Zorunlu kurallar

## 1. Manager ve uygulama yüzeyi kesin ayrılmalıdır
- Manager route'ları uygulama route'ları içine gizlenmemelidir
- Public veya kullanıcıya açık yüzey ile operasyon yüzeyi karıştırılmamalıdır
- Manager controller'ları yalnızca manager bağlamında çalışmalıdır

## 2. Varsayılan yaklaşım: minimum yetki
- Geliştirilen her manager özelliği en düşük gerekli yetki ile tasarlanmalıdır
- "Nasıl olsa iç kullanım" yaklaşımı kabul edilmez
- Her endpoint herkese açık olacakmış gibi değil, tersine yalnızca gerekli operatöre açık olacakmış gibi düşünülmelidir

## 3. Kimlik doğrulama zorunludur
- Auth gerektiren manager alanları açık bırakılmamalıdır
- Token, oturum veya mevcut kimlik doğrulama yaklaşımı atlanmamalıdır
- Test kolaylığı bahanesiyle manager endpoint'leri korumasız bırakılmamalıdır

## 4. Yetkilendirme ayrıca düşünülmelidir
- Sadece giriş yapılmış olması yeterli kabul edilmemelidir
- Her işlem için yetki seviyesi ayrıca değerlendirilmelidir
- Okuma, listeleme, üretim, değiştirme, silme, tetikleme ve bakım işlemleri aynı yetki seviyesinde ele alınmamalıdır

## 5. Rate limit ve kötüye kullanım koruması düşünülmelidir
- Manager API'leri gerektiğinde throttle ile korunmalıdır
- Yüksek etkili işlemler tekrar tekrar çağrılabilir bırakılmamalıdır
- Deneme-yanılma ile brute force veya suistimal üretilebilecek yüzeyler sınırlanmalıdır

## 6. Hassas işlemler doğrulama ve onay gerektirmelidir
- Yedek alma, silme, üretim tetikleme, mail test, runtime kontrol, queue işlemi gibi yüksek etkili aksiyonlar ek doğrulama ile ele alınmalıdır
- Geri dönüşü olmayan aksiyonlar onaysız tek tık akışına bırakılmamalıdır
- "Kazara çalıştırma" riski düşünülmelidir

## 7. Audit izi bırakılmalıdır
- Kritik manager işlemleri mümkün olduğunca iz bırakmalıdır
- Kim ne yaptı, ne zaman yaptı, hangi hedef üzerinde yaptı soruları cevapsız kalmamalıdır
- Sessiz ve görünmez operasyon davranışları kabul edilmez

## 8. Girdi doğrulama ihmal edilmemelidir
- Manager işlemleri güvenli ortam varsayımıyla validationsız bırakılmamalıdır
- Parametreler kontrol edilmeden sistem komutu, dosya işlemi, mail testi veya üretim akışı tetiklenmemelidir
- İç kullanıcı da olsa veri güvenilmez kabul edilmelidir

## 9. Gizli bilgi sızdırılmamalıdır
- Manager ekranları veya API çıktıları gereksiz sistem bilgisi dökmemelidir
- Secret, token, tam environment bilgisi, hassas path veya iç hata detayları doğrudan gösterilmemelidir
- Debug kolaylığı adına hassas bilgi yaymak yasaktır

## 10. Tehlikeli işlemler açıkça işaretlenmelidir
- Yıkıcı veya operasyonel etkisi yüksek aksiyonlar UI üzerinde net biçimde ayrıştırılmalıdır
- Silme, sıfırlama, yeniden oluşturma, yedek geri yükleme, servis tetikleme gibi işlemler masum buton gibi sunulmamalıdır

# Yasaklar

Aşağıdakiler yasaktır:

- Manager endpoint'i authsuz bırakmak
- Public route içinde gizli admin işlemi açmak
- GET isteğiyle veri değiştiren veya sistem etkileyen işlem yapmak
- Hassas parametreleri validationsız geçirmek
- Secret veya tam hata bilgisini doğrudan ekrana basmak
- Tek tıkla geri dönüşsüz kritik işlem yapmak
- Audit izi gerektiren bir işlemi tamamen görünmez bırakmak
- "Sadece biz kullanıyoruz" diyerek güvenlik katmanlarını atlamak
- Manager API ile uygulama API'sini aynı güvenlik seviyesiyle ele almak

# Uygulama akışı

## Aşama 1: İşlemin hassasiyetini değerlendir
- Bu işlem neyi etkiliyor?
- Veri, sistem ayarı, dosya, servis veya operasyon akışına etkisi var mı?
- Geri dönüşü var mı?

## Aşama 2: Erişim modelini belirle
- Kim bu işlemi görebilir?
- Kim çalıştırabilir?
- Kim sadece sonucu görebilir?

## Aşama 3: Koruma katmanlarını belirle
- Auth gerekli mi?
- Yetki kontrolü gerekli mi?
- Throttle gerekli mi?
- Ek onay gerekli mi?
- Audit kaydı gerekli mi?

## Aşama 4: Girdi ve çıktı güvenliğini tasarla
- Hangi veriler doğrulanacak?
- Hangi veriler maskelenecek?
- Hangi hata detayları kullanıcıya gösterilmeyecek?

## Aşama 5: UI ve akış güvenliğini tamamla
- Kritik butonlar net ayrılıyor mu?
- Kullanıcı yanlışlıkla riskli işlem yapabilir mi?
- İşlem sonucu açık ve güvenli biçimde raporlanıyor mu?

## Aşama 6: Doğrula
- Endpoint authsuz erişilebiliyor mu?
- Yetki dışı kullanıcı çalıştırabiliyor mu?
- Hata durumunda hassas bilgi sızıyor mu?
- İşlem izi kalıyor mu?
- Aynı işlem kötüye kullanılabilir mi?

# Çıktı formatı

Model şu sırayla cevap vermelidir:

1. Manager işleminin amacı
2. Hassasiyet ve risk özeti
3. Erişim ve yetki modeli
4. Gerekli güvenlik katmanları
5. Uygulama planı
6. Kod değişiklikleri
7. Doğrulama özeti

Doğrudan kod yazmak yerine önce risk ve koruma çerçevesi netleştirilmelidir.

# Kabul kriterleri

Bu skill'e uygun bir manager geliştirmesi şu şartları sağlamalıdır:

- Manager ve uygulama yüzeyi ayrımı korunmuş olmalı
- Kimlik doğrulama ve gerekiyorsa yetkilendirme düşünülmüş olmalı
- Hassas işlemler için ek güvenlik veya onay katmanı tanımlanmış olmalı
- Girdi doğrulaması yapılmış olmalı
- Hassas veri sızıntısı engellenmiş olmalı
- Audit veya iz bırakma ihtiyacı değerlendirilmiş olmalı
- UI üzerinde riskli işlemler açıkça ayrıştırılmış olmalı

# Kirpi'ye özel not

Kirpi içinde manager geliştirmek, normal ekran geliştirmek değildir.

Burada yapılan hata yalnızca bir özellik hatası değil; tüm framework operasyon yüzeyini etkileyen bir açıklığa dönüşebilir.

Her manager geliştirmesi için şu soru sorulmalıdır:

"Bu özelliği yanlış kişi görse, yanlış kişi çalıştırsa veya yanlış veriyle tetiklense ne olur?"