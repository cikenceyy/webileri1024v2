<?php

namespace App\Core\TableKit;

use Closure;
use InvalidArgumentException;

/**
 * TableKit export işlemleri için modüllerin kayıt olmasını sağlayan kayıt defteri.
 * Amaç: ExportJob çalıştığında ilgili sorgu üreticisini çözebilmek.
 */
class TableExporterRegistry
{
    /** @var array<string, Closure> */
    private array $exporters = [];

    /**
     * Export işlemi için modül callback'ini kaydeder.
     * Callback, builder/config/kolon bilgilerinden oluşan bir dizi döndürmelidir.
     */
    public function register(string $tableKey, Closure $resolver): void
    {
        $this->exporters[$tableKey] = $resolver;
    }

    /**
     * Kayıtlı export resolver'ını döndürür.
     */
    public function resolve(string $tableKey): Closure
    {
        if (! isset($this->exporters[$tableKey])) {
            throw new InvalidArgumentException("{$tableKey} export için tanımlı değil.");
        }

        return $this->exporters[$tableKey];
    }
}
