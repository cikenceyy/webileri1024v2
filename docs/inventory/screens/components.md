# Üründe Kullanılan Malzemeler

## Data-action sözleşmeleri
- `data-components-lot` — lot seçici formu; değişimde kartlar yeniden hesaplanır.
- `data-component-card` — her bileşen kartı; `data-base-qty`, `data-on-hand` ile hesaplama yapılır.
- `data-action="components-resolve"` — eksikleri çözmek için tetiklenen aksiyonlar (`data-component-id`, `data-action-type`).

## Kısayollar
Kısayol tanımlı değildir.

## Notlar
- Lot değişiminde kart sınıfı `inv-components__card--insufficient` olarak güncellenir.
- `inventory:components:resolve` olayı üst sepet/tedarik akışına bağlanmak için kullanılabilir.
