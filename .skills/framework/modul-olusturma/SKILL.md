---
name: modul-olusturma
description: Kirpi Framework içinde yeni modül oluştururken mimari yapıyı, klasör düzenini, route yaklaşımını ve üretim disiplinini korumayı sağlar.
---

# Amaç

Bu skill, Kirpi Framework üzerinde yeni bir modül oluşturulurken standart, güvenli ve sürdürülebilir bir yapı kurulmasını sağlar.

Amaç; her modülün aynı kalite ve aynı mimari disiplinle oluşturulmasını garanti altına almaktır.

# Ne zaman kullanılır?

- Yeni bir işlevsel modül oluşturulurken
- Yeni bir domain (örneğin: teklifler, müşteriler, ürünler vb.) eklenirken
- Mevcut olmayan bir iş süreci sisteme dahil edilirken

# Zorunlu kurallar

## 1. Modül mimariye uymalıdır
- Modül, `Modules` yapısı içinde konumlandırılmalıdır
- Core veya Manager alanlarına gereksiz müdahale edilmemelidir
- Modül kendi sorumluluğunu taşır, başka modüllerin alanına taşmaz

## 2. Katmanlı yapı korunmalıdır
Bir modül aşağıdaki katmanları içermelidir (ihtiyaca göre):

- Controller → sadece yönlendirme ve orchestration
- Service → iş mantığı burada
- Request / Validation → veri doğrulama
- Repository (varsa) → veri erişimi
- View → sadece sunum

Controller içinde business logic yazmak yasaktır.

## 3. Route standardı korunmalıdır
- Route tanımları mevcut Kirpi yaklaşımına uygun olmalıdır
- Admin/manager route’ları ile public route’lar karıştırılmamalıdır
- Route isimleri anlamlı ve tutarlı olmalıdır

## 4. UI standardına uyulmalıdır
- Tabler tema kullanılmalıdır
- Responsive yapı korunmalıdır
- Dark/Light mode uyumu bozulmamalıdır
- Toastr bildirim sistemi kullanılmalıdır

## 5. Test / demo yaklaşımı düşünülmelidir
- Yeni modül UI içeriyorsa test page yaklaşımına uygun demo hazırlanmalıdır
- Kullanıcı akışı test edilebilir olmalıdır

## 6. Mevcut komut ve üretim araçları kullanılmalıdır
- Mümkünse `make:module`, `make:crud` gibi araçlar kullanılmalıdır
- Framework zaten çözmüş olduğu şeyi yeniden elle yazmak yasaktır

# Yasaklar

- Controller içine yoğun iş mantığı yazmak
- Modül oluştururken Core'u gereksiz değiştirmek
- Manager yapısını bypass ederek gizli admin endpoint'leri oluşturmak
- UI tarafında Tabler dışında rastgele yapı kurmak
- Validation'ı tamamen atlamak
- Tüm kodu tek dosyada çözmeye çalışmak
- Mevcut modül yapısına benzemeyen yeni klasör düzeni icat etmek

# Uygulama akışı

## Aşama 1: İhtiyacı anla
- Bu modül neyi çözüyor?
- Hangi kullanıcı tipine hitap ediyor?

## Aşama 2: Etki analizi yap
- Yeni modül mü yoksa mevcut modüle ek mi?
- Hangi route'lar gerekecek?
- UI var mı?

## Aşama 3: Modül planını çıkar
- Hangi dosyalar olacak?
- Controller, Service, Validation ayrımı nasıl yapılacak?
- Veri modeli ne olacak?

## Aşama 4: Kod üret
- Önce iskelet oluştur
- Sonra iş mantığını ekle
- UI varsa standarda uygun geliştir

## Aşama 5: Doğrula
- Route çalışıyor mu?
- Validation düzgün mü?
- UI responsive mi?
- Bildirimler doğru mu?
- Dark/Light mode bozuldu mu?

# Çıktı formatı

Model şu sırayla cevap vermelidir:

1. Modülün amacı
2. Etkilenen alanlar
3. Dosya yapısı (liste)
4. Uygulama planı
5. Kod

# Kabul kriterleri

- Modül Kirpi mimarisine uygun olmalı
- Katmanlı yapı korunmalı
- UI standardı bozulmamalı
- Route ve erişim mantığı doğru kurulmalı
- Kod okunabilir ve sürdürülebilir olmalı
- Framework içinde uyumsuzluk yaratmamalı

# Kirpi'ye özel not

Kirpi'de modül geliştirmek sadece yeni kod eklemek değildir.

Amaç; framework içinde yaşayan, sürdürülebilir ve diğer modüllerle uyumlu çalışan bir yapı oluşturmaktır.

Her modül şu soruyu geçmelidir:

"Bu modül Kirpi'nin bir parçası mı, yoksa içine sonradan eklenmiş yabancı bir parça mı?"
