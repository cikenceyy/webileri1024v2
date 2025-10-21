# Geliştirici Notları

## Kurulum & Cache

1. Ortam dosyasını oluşturmak için `cp .env.example .env` komutunu çalıştırın ve gerekirse düzenleyin.
2. SQLite veritabanı dosyasını hazırlamak için `touch database/database.sqlite` komutunu çalıştırın.
3. Bağımlılıkları ve uygulama anahtarını hazırlamak için sırasıyla `composer install`, `php artisan key:generate` ve `php artisan migrate --seed` komutlarını çalıştırın.
4. Konfigürasyonun önbelleğe alınmasını doğrulamak için `php artisan config:cache`, rotalar için `php artisan route:cache` ve görünümler için `php artisan view:cache` komutlarının hatasız tamamlandığından emin olun.
