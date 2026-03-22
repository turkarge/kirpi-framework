# 07 - Yedekleme ve Geri Donus

## Backup Tipleri

- `db`: yalnizca veritabani dump
- `full`: db + `.env` + `storage/app` snapshot

## Backup Akisi

1. Manager Backup Center'dan backup olustur
2. zip + sha256 dosyasi uretilir
3. backup listesinde gorunur
4. verify ile checksum dogrulanir
5. download ile dis ortama alin

## Retention

`KIRPI_BACKUP_RETENTION` kadar backup tutulur, fazlasi otomatik temizlenir.

## Restore Stratejisi (onerilen)

1. Uygun backup zip'i indir
2. Izole ortamda once DB restore dene
3. Sonra production'a kontrollu gec
4. Restore prosedurunu dokumante et

## Operasyon Kurali

Backup alinmissa restore testi de duzenli yapilmalidir.
Aksi halde backup tek basina guvence saglamaz.
