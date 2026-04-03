---
name: route-ve-adlandirma
description: Kirpi Framework içinde route tanımları, controller isimlendirmesi ve URL yapısının tutarlı, okunabilir ve güvenli şekilde oluşturulmasını sağlar.
---

# Amaç

Bu skill, Kirpi Framework içinde route tanımları ve adlandırma standartlarının dağılmasını önler.

Amaç; tüm endpoint’lerin, controller’ların ve URL yapısının öngörülebilir, tutarlı ve sürdürülebilir olmasını sağlamaktır.

# Ne zaman kullanılır?

- Yeni route eklenirken
- Yeni controller oluşturulurken
- CRUD endpoint’leri tanımlanırken
- Manager veya admin endpoint eklenirken
- Mevcut route yapısı refactor edilirken

# Kirpi bağlamı

Kirpi Framework içinde route yapısı iki ana düzleme ayrılır:

- Uygulama (Modules / public yüzey)
- Manager (operasyonel kontrol yüzeyi)

Bu iki alan kesin olarak ayrılmalıdır.

# Zorunlu kurallar

## 1. Route yapısı anlamlı olmalıdır
- URL, endpoint amacını açıkça ifade etmelidir
- Rastgele kısaltmalar kullanılmamalıdır
- Okunabilirlik önceliklidir

Yanlış:
- /prj
- /x-list

Doğru:
- /projects
- /projects/list

## 2. Controller isimleri tutarlı olmalıdır
- Controller isimleri modül ve işlemle uyumlu olmalıdır

Örnek:
- ProjectController
- ProjectCrudController
- ProjectAdminController

## 3. Route ve controller uyumu korunmalıdır
- Route adı ile controller sorumluluğu örtüşmelidir
- Controller içinde farklı domain işlemleri karıştırılmamalıdır

## 4. HTTP method doğru kullanılmalıdır

- GET → veri çekme
- POST → oluşturma
- PUT/PATCH → güncelleme
- DELETE → silme

GET ile veri değiştirme yapılmaz.

## 5. Manager ve uygulama route’ları ayrılmalıdır

- Manager endpoint’ler ayrı namespace ve route yapısında olmalıdır
- Uygulama route’ları ile karıştırılmamalıdır

Yanlış:
- /projects/admin/delete

Doğru:
- /manager/projects/delete

## 6. Route isimlendirmesi standardize edilmelidir

Önerilen yapı:

- projects.index
- projects.create
- projects.store
- projects.edit
- projects.update
- projects.delete

## 7. Parametre kullanımı açık olmalıdır

- ID veya slug parametreleri açık şekilde belirtilmelidir

Örnek:
- /projects/{id}
- /projects/{id}/edit

## 8. Middleware kullanımı ihmal edilmemelidir

- Auth gerektiren route’lar açık bırakılmamalıdır
- Manager route’lar ekstra güvenlik katmanları ile korunmalıdır

# Yasaklar

- Anlamsız kısaltmalarla route yazmak
- GET ile veri silmek veya değiştirmek
- Manager endpoint’i public route içine koymak
- Controller içinde birden fazla domain işini karıştırmak
- Route ve controller isimlerini alakasız seçmek
- Aynı işi yapan farklı isimde endpoint’ler üretmek

# Uygulama akışı

## Aşama 1: Endpoint ihtiyacını belirle
- Hangi işlem yapılacak?
- Hangi modüle ait?

## Aşama 2: URL tasarla
- Okunabilir mi?
- Anlaşılır mı?
- Tutarlı mı?

## Aşama 3: Controller belirle
- Sorumluluk net mi?
- Başka işlerle karışıyor mu?

## Aşama 4: Route tanımla
- Doğru HTTP method kullanıldı mı?
- Middleware eklendi mi?

## Aşama 5: Doğrula
- Route mantıklı mı?
- İsimlendirme tutarlı mı?
- Manager/app ayrımı doğru mu?

# Çıktı formatı

Model şu sırayla cevap vermelidir:

1. Endpoint amacı
2. Önerilen route listesi
3. Controller yapısı
4. Kullanılacak HTTP methodlar
5. Güvenlik ve middleware notları
6. Kod örneği

# Kabul kriterleri

- Route isimleri açık ve anlaşılır olmalı
- Controller sorumluluğu net olmalı
- HTTP method doğru kullanılmalı
- Manager ve uygulama ayrımı korunmalı
- Endpoint yapısı tutarlı olmalı

# Kirpi'ye özel not

Kirpi içinde route yazmak sadece endpoint tanımlamak değildir.

Amaç; sistemin dışa açılan yüzünü düzenli, okunabilir ve güvenli tutmaktır.

Her route için şu soru sorulmalıdır:

"Bu endpoint’i ilk kez gören biri ne yaptığını anlar mı?"