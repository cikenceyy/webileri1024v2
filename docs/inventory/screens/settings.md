# Inventory Ayarları

## Data-action sözleşmeleri
- `data-settings-tab` — sekmeli gezinme; `data-active-tab` host üzerinde güncellenir.
- `data-settings-node` — sol ağaç/liste düğümleri; `data-id` ve `data-type` detay sorgusunda kullanılır.
- `data-action="settings-bulk"` — toplu işlemler (`data-action-type`).
- Detay bölgesi `data-detail-region` ve `data-endpoint` öznitelikleriyle AJAX yükleme yapar.

## Kısayollar
Bu ekranda kısayol bulunmamaktadır.

## Notlar
- Her seçim `inventory:settings:*` özel olayları ile diğer scriptlere yayımlanır.
- Bulk aksiyon tetiklenmeden önce kullanıcı doğrulaması Blade/Modal katmanında yapılmalıdır.
