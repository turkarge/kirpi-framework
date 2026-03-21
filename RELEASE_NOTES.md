# Kirpi Framework - Release Note Standardi

Bu dosya, her push/iterasyon sonunda paylasilacak release note formatini standartlastirir.

## 1) Baslik
Format:
`[Tarih] [Alan] Kisa Degisiklik Ozeti`

Ornek:
`2026-03-21 Frontend: Tabler shell patch katmani stabilize edildi`

## 2) Zorunlu Bolumler
Her release note su 6 bolumu icermelidir:

1. `Neden?`
- Bu degisiklik hangi problemi cozuyor?

2. `Ne Degisti?`
- Teknik olarak hangi dosya/sinif/davranis degisti?

3. `Etkilenen Yuzey`
- Hangi route/komut/modul etkilendi?

4. `Geriye Donuk Etki`
- Breaking change var mi?
- Migration/env/config degisikligi gerekiyor mu?

5. `Dogrulama`
- Hangi test/komut calisti?
- Sonuc nedir?

6. `Commit/Push`
- Commit SHA
- Commit mesaji

## 3) Yazim Kurallari
- Pazarlama dili kullanma; teknik ve dogrulanabilir yaz.
- "Iyilestirildi", "duzeltildi" gibi genel ifadeler yerine somut davranis degisikligi yaz.
- Dosya referanslarini mutlak path ile ver.
- Test bolumu bos gecilemez. Test calismadiysa sebebi acik yazilir.

## 4) Kapsam Etiketleri
Notun basliginda su etiketlerden biri kullanilir:
- `Core`
- `Routing`
- `Database`
- `Auth`
- `Frontend`
- `Runtime`
- `Tests`
- `Docs`

## 5) Kisa Release Note Sablonu
```md
## [Tarih] [Etiket] Baslik

### Neden?
- ...

### Ne Degisti?
- ...

### Etkilenen Yuzey
- Route/Modul: ...
- Dosyalar:
  - ...

### Geriye Donuk Etki
- Breaking: Yok/Var (...)
- Ek adim: Yok/Var (...)

### Dogrulama
- `...komut...` -> Sonuc: ...

### Commit/Push
- SHA: `...`
- Mesaj: `...`
```

## 6) Uygulama Karari
- Bu standart, Kirpi Framework'de **varsayilan release note formati** olarak kullanilir.
- TODO guncellemesi yapilan her iterasyonda bu formatla not cikilir.
