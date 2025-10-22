# Inventory UI Kit

Bu doküman, Inventory modülünde kullanılan arayüz bileşenlerinin ad, sınıf ve davranış sözleşmelerini özetler. Tüm bileşenler Bootstrap 5 bileşenleriyle uyumlu olup, `/resources` altındaki renk, tipografi, espas ve radius token'larıyla stillenmelidir.

## Kartlar
- **Taban sınıf:** `inv-card`
- **Varyantlar:**
  - `inv-card--kpi`
  - `inv-card--product`
  - `inv-card--lowstock`
  - `inv-card--action`
- **Alt eleman örnekleri:** `__meta`, `__value`, `__actions`
- **Davranış:** Kartlar `aria-live` bölgeleri veya etkileşimli butonlar içerebilir. Hover ve focus durumlarında `box-shadow` ve `border` tonları güçlendirilir.

## Chip ve Badge'ler
- **Sınıflar:** `inv-chip`, `inv-chip--filter`, `inv-chip--state`
- **Data attribute:** `data-chip-action="toggle-filter"`
- **Etkileşim:** Chip'ler `is-active` sınıfıyla seçili durumu gösterir ve GET parametresi güncellenir.

## Mini Timeline
- **Sınıf:** `inv-timeline`
- **Öğe sınıfı:** `inv-timeline__item`
- **Davranış:** Her öğe işlem başlığı, alt metin ve saat bilgisini içerir; güncelleme `Home` modülü tarafından yapılır.

## Depo Izgarası (Heatmap)
- **Sınıf:** `inv-heat__cell`
- **Data attribute'lar:**
  - `data-level="0-5"`
  - `data-rack`, `data-level`
  - `data-items` (JSON string)
- **Davranış:** Hücre seçiminde `data-selected="true"` atanır ve sağ panel güncellenir.

## Keypad
- **Sınıf:** `inv-keypad`
- **Tuğla sınıfı:** `inv-keypad__key`
- **Data attribute:** `data-key="0-9|.|del|plus|minus"`
- **Davranış:** `StockConsole` modülü `data-key` değerini okuyarak miktar durumunu yönetir.

## Modal/Sheet
- **Sınıf:** `inv-sheet`
- **Data attribute:** `data-sheet="lowstock"`
- **Davranış:** Sheet açık olduğunda `is-open` sınıfı eklenir, `data-action="sheet-dismiss"` butonları kapanışı tetikler.

## Heatmap Hücresi
- **Sınıf:** `inv-heat__cell`
- **Data attribute:** `data-level`, `data-selected`
- **Davranış:** Seçili hücrelerde outline ve gölge güçlendirilir; yoğunluğa göre arka plan tonu güncellenir.

## Erişilebilirlik Notları
- Tüm butonlar ve chip'ler `aria-label` veya ekran okuyucular için metin içerir.
- Fokus halkaları devre dışı bırakılmaz; `:focus-visible` durumunda kontrastlı outline sağlanır.
- 44px dokunmatik hedef kuralı keypad, hızlı aksiyon ve kart eylemlerinde sağlanır.
