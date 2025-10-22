# Inventory Home (Kontrol Kulesi)

## Data-action sözleşmeleri
- `data-action="inventory-quick"` — hızlı aksiyon kartları; `data-mode` değeri konsol sekmesini (`in|out|transfer|adjust`) veya ürün ekleme yönlendirmesini belirler.
- `data-action="inventory-lowstock"` — düşük stok kartı, sheet içeriğini tetikler.
- `data-action="inventory-lowstock-transfer"` ve `data-action="inventory-lowstock-procure"` — sheet içindeki hızlı eylem butonları.
- `data-action="sheet-dismiss"` — alt sheet kapatma kontrolleri.

## Kısayollar
Bu ekranda klavye kısayolu tanımlı değildir; tüm aksiyonlar fare/dokunmatik ile tetiklenir.

## Notlar
- KPI, timeline ve düşük stok bileşenleri `data-*-endpoint` öznitelikleriyle XHR üzerinden güncellenir.
- Sheet açıldığında `aria-hidden` değeri güncellenir; önerilen miktar ve hedef depo otomatik doldurulur.
- Sheet üzerindeki depo seçimi backend'den gelen gerçek depo listesiyle doldurulur.
