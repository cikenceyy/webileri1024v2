# UX Guidelines – Consoles

Bu rehber, `/consoles/*` ekranlarında uygulanacak tasarım kalıplarını ve erişilebilirlik kriterlerini toplar.

## Sayfa İskeleti

- Tüm konsol sayfaları `resources/views/layouts/admin.blade.php` layout'u üzerinden çalışır; `data-module="Consoles"` ve `data-page` attribute’ları dinamik import akışına bilgi sağlar.
- Üst blokta (başlık + filtreler) Bootstrap grid ile hizalanmış form elemanları kullanılır; form `GET` parametreleri `O2CQueryRequest`, `P2PQueryRequest`, `MTOQueryRequest` sınıflarında tanımlıdır.
- KPI alanı dört karttan oluşur, her kart `card` + `display-6` tipografisiyle sayısal gösterge sunar.

## Pipeline Tablosu Kalıbı

- Her aksiyon kartı `<table class="table table-sm align-middle">` ile render edilir.
- Satır başlığında referans numarası ve ikincil bilgi (müşteri, termin vb.) `<small class="text-muted">` içinde gösterilir.
- Aksiyon butonu `btn btn-sm btn-outline-primary` ve POST formu ile sarılmıştır; CSRF token otomatik eklenir.
- Butonlar yalnızca orchestrasyon tarafından desteklenen alanları gönderir (ör. `order_id`, `shipment_id`). Gerekirse modal tabanlı detay formları ileride `@push('page-scripts')` ile genişletilebilir.

## Erişilebilirlik

- Her tablo başlığı `<th scope="col">` ile tanımlanır; tablo satırları minimal ama screen reader dostu `<div class="fw-semibold">` + `<small>` kombinasyonuna sahiptir.
- Filtre formları etiket (`<label>`) ile input eşleştirmesine dikkat eder; form kontrol boyutları `.form-control-sm` olup klavye erişimini engellemez.
- Toast bildirimi `x-ui.toast-stack` bileşeni layout içinde global olarak bulunur; işlemler geri bildirim mesajını session flash üzerinden iletir.

## Renk ve İkonografi

- Kart gövdeleri Bootstrap varsayılan renkleriyle kullanılır, modül temalı override gerektirmez. Gerekirse `resources/scss/pages/consoles` altına stil eklenebilir.
- Aksiyon butonları nötr (outline-primary) seçildi, böylece kullanıcı adımı başlatmadan önce bilgilendirilmiş olur.

## Performans Notları

- Konsol sayfaları yalnızca gerekli liste verilerini çeker; `preview()` metodları limit=5 ile pipeline listelerini küçültür.
- Vite dinamik import akışı `module="Consoles"` için hata vermeden düşecek şekilde tasarlandı (admin.js `catch()` bloğu); ileride özel JS eklenecekse `resources/js/pages/consoles/*` dizini kullanılabilir.
