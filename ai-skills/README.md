# Kirpi AI Skill Sistemi

Bu klasor, Kirpi Framework icin tum AI editorlerde ortak ve tutarli calisan bir skill sisteminin tek dogru kaynagidir.

## Hedef

- Tek bir canonical kural seti
- Editor bazli minimum fark
- Kod yazarken surpriz davranis olmamasi
- Tabler tabanli UI standartlarinin korunmasi

## Dosya Yapisi

- `SKILL_CONTRACT.md` -> tum editorler icin ana kural sozlesmesi
- `manifest.json` -> skill paketlerinin listesi ve versiyonu
- `skills/*` -> moduler beceri paketleri
- `adapters/*` -> editorlere ozel kisa yonlendirme dosyalari

## Kullanim Sirasi (zorunlu)

1. `SKILL_CONTRACT.md`
2. goreve uygun `skills/*.md`
3. editor adapter dosyasi

## Temel Ilke

Editor degisse de davranis degismez.

