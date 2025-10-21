<?php

namespace App\Modules\Drive\Database\Seeders;

use App\Core\Support\Models\Company;
use App\Modules\Drive\Domain\Models\Media;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DriveDemoSeeder extends Seeder
{
    public function run(): void
    {
        $companies = Company::query()->get();

        if ($companies->isEmpty()) {
            return;
        }

        $disk = config('filesystems.default', 's3');
        $storage = Storage::disk($disk);

        $definitions = [
            Media::CATEGORY_DOCUMENTS => [
                ['original_name' => 'Satis-Sozlesmesi.pdf', 'ext' => 'pdf', 'mime' => 'application/pdf', 'content' => 'Demo PDF içerik', 'important' => true],
                ['original_name' => 'Tedarikci-Listesi.xlsx', 'ext' => 'xlsx', 'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'content' => 'Demo XLSX içerik'],
                ['original_name' => 'Personel-Rehberi.docx', 'ext' => 'docx', 'mime' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'content' => 'Demo DOCX içerik'],
                ['original_name' => 'Fiyat-Tablosu.csv', 'ext' => 'csv', 'mime' => 'text/csv', 'content' => "sku,price\nDEMO-1,99"],
            ],
            Media::CATEGORY_MEDIA_PRODUCTS => [
                ['original_name' => 'urun-01.jpg', 'ext' => 'jpg', 'mime' => 'image/jpeg', 'content' => 'Ürün görseli 01', 'important' => true],
                ['original_name' => 'urun-02.png', 'ext' => 'png', 'mime' => 'image/png', 'content' => 'Ürün görseli 02'],
                ['original_name' => 'urun-03.webp', 'ext' => 'webp', 'mime' => 'image/webp', 'content' => 'Ürün görseli 03'],
            ],
            Media::CATEGORY_MEDIA_CATALOGS => [
                ['original_name' => 'katalog-2024.pdf', 'ext' => 'pdf', 'mime' => 'application/pdf', 'content' => 'Katalog 2024', 'important' => true],
                ['original_name' => 'lookbook.jpg', 'ext' => 'jpg', 'mime' => 'image/jpeg', 'content' => 'Lookbook görseli'],
                ['original_name' => 'brosur.png', 'ext' => 'png', 'mime' => 'image/png', 'content' => 'Broşür görseli'],
            ],
            Media::CATEGORY_PAGES => [
                ['original_name' => 'landing.html', 'ext' => 'html', 'mime' => 'text/html', 'content' => '<h1>Demo</h1>'],
                ['original_name' => 'seo.json', 'ext' => 'json', 'mime' => 'application/json', 'content' => '{"title":"Demo"}'],
            ],
        ];

        $period = now()->format('Y/m');

        foreach ($companies as $company) {
            $uploaderId = User::query()->where('company_id', $company->id)->value('id');

            foreach ($definitions as $category => $files) {
                foreach ($files as $index => $file) {
                    $folder = sprintf('company/%s/%s/demo/%s', $company->id, $category, $period);
                    $filename = ($index + 1) . '-' . Str::slug(pathinfo($file['original_name'], PATHINFO_FILENAME)) . '.' . $file['ext'];
                    $path = $folder . '/' . $filename;

                    if (! $storage->exists($path)) {
                        try {
                            $storage->put($path, $file['content'] ?? 'demo');
                        } catch (\Throwable $exception) {
                            report($exception);
                            continue;
                        }
                    }

                    $size = $storage->exists($path) ? ($storage->size($path) ?: strlen($file['content'] ?? 'demo')) : strlen($file['content'] ?? 'demo');
                    $hash = hash('sha256', $file['content'] ?? 'demo');

                    Media::query()->updateOrCreate(
                        [
                            'company_id' => $company->id,
                            'category' => $category,
                            'path' => $path,
                        ],
                        [
                            'disk' => $disk,
                            'original_name' => $file['original_name'],
                            'mime' => strtolower($file['mime']),
                            'ext' => strtolower($file['ext']),
                            'size' => $size,
                            'sha256' => $hash,
                            'width' => null,
                            'height' => null,
                            'thumb_path' => null,
                            'is_important' => (bool) ($file['important'] ?? false),
                            'uploaded_by' => $uploaderId,
                        ]
                    );
                }
            }
        }
    }
}
