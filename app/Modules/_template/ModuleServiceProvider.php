<?php

namespace App\Modules\_template;

use Illuminate\Support\ServiceProvider;

/**
 * Modül şablonu için servis sağlayıcı iskeleti.
 * Bu sınıfı yeni modül klasörüne kopyaladıktan sonra isim alanını ve
 * register/boot yöntemlerini ilgili modülün gereksinimlerine göre
 * güncelleyin.
 */
class ModuleServiceProvider extends ServiceProvider
{
    /**
     * Modüle ait container binding tanımlarını gerçekleştirmek için kullanılır.
     * Kopyaladıktan sonra ilgili repository, servis veya policy bağlamalarını
     * burada yapabilirsiniz.
     */
    public function register(): void
    {
        // TODO: Modül bağımlılıklarını burada container'a bağlayın.
    }

    /**
     * Rotalar, view dizinleri, çeviriler ve publish edilecek varlıklar gibi
     * modül kurulumlarını burada gerçekleştirin.
     */
    public function boot(): void
    {
        // TODO: routes/admin.php ve routes/web.php dosyalarını burada yükleyin.
    }
}
