# Inventory Ekran Davranış Şemaları

Basitleştirilmiş akış diyagramları, temel kullanıcı etkileşimlerini ve sonuçlarını gösterir.

## Home (Kontrol Kulesi)
```
[Kullanıcı] --> (Hızlı Aksiyon Seç) --> {Stock Console modu?}
    yes --> [Route: admin.inventory.stock.console?mode=tab]
[Kullanıcı] --> (Düşük Stok Kartı) --> [Low-stock sheet açılır] --> (Hedef depo seç) --> [Önerilen miktar göster]
```

## Stok İşlem Konsolu
```
[Başlangıç] --> (Sekme seçimi) --> [Mode state güncellendi]
[Mode state] --> (Ürün arama/barkod) --> [Sepete ürün eklendi]
[Sepet] --> (Keypad / +/-) --> {Negatif stok?}
    yes --> [Uyarı göster]
    no --> (Kaydet)
(Kaydet) --> [POST /admin/inventory/stock-console]
    --> {Başarı?}
        yes --> [Sepet boşaltılır + başarı vurgusu]
        no --> [Hata bildirimi]
```

## Ürün Detay
```
[Kullanıcı] --> (Varyant pill) --> [Query param güncellenir]
[Kullanıcı] --> (Depo matrisi hücresi) --> [Tooltip stok özetini gösterir]
[Kullanıcı] --> (Hızlı aksiyon) --> [inventory:product:* olayı tetiklenir]
```

## Depo Detay
```
[Kullanıcı] --> (Raf hücresi seç) --> [Seçili durum güncellenir]
    --> [Sağ panel ürün listesi yenilenir]
    --> (Transfer/Düzelt/Etiket) --> [İlgili sheet/modal açılır]
```

## Fiyat Listesi
```
[Kullanıcı] --> (Kalem ekle) --> [Şablon satır kopyalanır]
[Kullanıcı] --> (Simülasyon alanı) --> [Toplam hesaplanır ve rozet güncellenir]
```

## BOM
```
[Kullanıcı] --> (Lot seçimi) --> [Lot büyüklüğü -> kalem hesap]
    --> {Eksik stok?}
        yes --> [inv-bom__row--insufficient sınıfı + çözüm butonları]
        no --> [Satır normal durumda]
```

## Üründe Kullanılan Malzemeler
```
[Kullanıcı] --> (Lot formu) --> [Kart bazında gereken miktar güncellenir]
    --> {Eksik?}
        yes --> [inv-components__card--insufficient]
        no --> [Standart kart]
```

## Inventory Ayarları
```
[Kullanıcı] --> (Sekme) --> [Aktif sözlük değişir]
[Kullanıcı] --> (Ağaç düğümü) --> [Detay paneli AJAX ile yüklenir]
[Kullanıcı] --> (Toplu aksiyon) --> [inventory:settings:bulk olayı tetiklenir]
```
