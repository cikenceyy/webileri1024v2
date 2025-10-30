# Freshness Middleware ve 304 Politikası

ConsoleKit ve ColdReports uçları yüksek tekrar oranına sahip olduğu için yanıt başlıklarında **ETag** ve **Last-Modified** kullanıyoruz. Tarayıcı ya da edge cache aynı veriyi 10-30 saniyelik pencereler içerisinde yeniden talep ederse middleware 304 (Not Modified) döndürür; böylece CPU ve sorgu maliyeti düşer.

- JSON veya CSV dönen GET uçları `fresh` alias'lı `FreshnessMiddleware` ile korunmalıdır.
- Backend yanıtı `X-Freshness-Timestamp` başlığını taşıyorsa aynı değer Last-Modified olarak da işaretlenir.
- İstemciler tekrar istek attıklarında `If-None-Match` ya da `If-Modified-Since` başlığı otomatik gönderilir; veri değişmediyse boş gövdeli 304 cevap alınır.
- Uzun süreli cache gerektiren raporlar için snapshot üretildikten sonra `ReportRegistry::markFresh` ile tahmini tazelik süresi güncellenir; UI rozetleri bu bilgiyi gösterir.

Bu politika sıcak (hot) cache katmanını tamamlar ve edge katmanında gereksiz istekleri azaltır.
