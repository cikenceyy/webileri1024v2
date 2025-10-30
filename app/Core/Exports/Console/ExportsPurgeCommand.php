<?php

namespace App\Core\Exports\Console;

use App\Core\Exports\Models\TableExport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Export kayıtlarını ve dosyalarını belirli sürenin öncesinde temizler.
 */
class ExportsPurgeCommand extends Command
{
    protected $signature = 'exports:purge {--before=30}';

    protected $description = 'Export kayıtlarını ve ilgili dosyaları temizler.';

    public function handle(): int
    {
        $days = (int) $this->option('before');
        $threshold = now()->subDays($days);

        $this->info('Export temizliği başlıyor: ' . $threshold->toDateTimeString());

        $exports = TableExport::query()
            ->where('created_at', '<', $threshold)
            ->get();

        foreach ($exports as $export) {
            if ($export->file_path && Storage::disk('local')->exists($export->file_path)) {
                Storage::disk('local')->delete($export->file_path);
            }

            $export->delete();
        }

        $this->info('Temizlik tamamlandı. Silinen kayıt sayısı: ' . $exports->count());

        return self::SUCCESS;
    }
}
