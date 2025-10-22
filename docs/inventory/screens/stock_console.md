# Stok İşlem Konsolu

## Data-action sözleşmeleri
- `data-console-tab` — sekme linkleri; JS modu günceller.
- `data-action="product-search"` — barkod/arama alanı.
- `data-action="cart-select"` — öneri kartı veya sepet satırı odaklama.
- `data-action="qty-adjust"` — miktar +/- butonları.
- `data-cart-qty` — sepet satırındaki sayı girdisi.
- `data-action="cart-remove"` — satırı kaldırır.
- `data-action="console-submit"`, `console-reset`, `console-print`, `console-share` — alt aksiyon butonları.
- `data-console-feedback` — işlem mesajlarının gösterildiği uyarı alanı.

## Kısayollar
- `Ctrl + I/O/T/D` — ilgili işlem sekmesine geçiş.
- `Enter` — aktif kalem miktarını onaylar.
- `Esc` — aktif kalemi bırakır.

## Notlar
- `data-allow-negative` özniteliği stok negatife düşüş kontrolünü belirler.
- Sayısal keypad `data-key` değerleriyle çalışır; tampon değer `inputBuffer` olarak saklanır.
- Sepet özeti `data-summary-lines/qty/value` ile güncellenir.
- Düzeltme modunda miktarlar negatif değer alabilir; JS tarafı stok yetersizliklerinde inline uyarı üretir.
