# Ürün Reçeteleri (BOM)

## Data-action sözleşmeleri
- `data-action="bom-lot"` — lot seçici butonları; `data-value` lot büyüklüğünü belirtir.
- `data-action="bom-resolve"` — eksik kalem aksiyonları (`data-material-id`, `data-action-type`).
- BOM satırları `data-base-qty`, `data-on-hand`, `data-field="required|shortage"` öznitelikleriyle güncellenir.

## Kısayollar
Kısayol bulunmamaktadır.

## Notlar
- Lot değişiminde satır sınıfı `inv-bom__row--insufficient` olarak güncellenir ve eksik miktar hesaplanır.
- `inventory:bom:resolve` olayı tetiklenerek üst bileşenler gerekli işlemleri dinleyebilir.
