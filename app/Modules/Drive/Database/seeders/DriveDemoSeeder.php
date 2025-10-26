<?php

namespace App\Modules\Drive\Database\Seeders;

use App\Core\Support\Models\Company;
use App\Modules\Drive\Domain\Models\Media;
use App\Modules\Drive\Support\DriveStructure;
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
            'documents' => [
                ['original_name' => 'Satis-Sozlesmesi.pdf', 'ext' => 'pdf', 'mime' => 'application/pdf', 'content' => 'Demo PDF içerik', 'important' => true],
                ['original_name' => 'Tedarikci-Listesi.xlsx', 'ext' => 'xlsx', 'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'content' => 'Demo XLSX içerik'],
                ['original_name' => 'Personel-Rehberi.docx', 'ext' => 'docx', 'mime' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'content' => 'Demo DOCX içerik'],
                ['original_name' => 'Fiyat-Tablosu.csv', 'ext' => 'csv', 'mime' => 'text/csv', 'content' => "sku,price\nDEMO-1,99"],
            ],
            'media' => [
                ['original_name' => 'tanitim-afisi.jpg', 'ext' => 'jpg', 'mime' => 'image/jpeg', 'content' => 'Tanıtım afişi'],
                ['original_name' => 'kampanya-afisi.png', 'ext' => 'png', 'mime' => 'image/png', 'content' => 'Kampanya afişi'],
                ['original_name' => 'brosur.pdf', 'ext' => 'pdf', 'mime' => 'application/pdf', 'content' => 'Broşür'],
            ],
            'products' => [
                ['original_name' => 'urun-01.jpg', 'ext' => 'jpg', 'mime' => 'image/jpeg', 'content' => 'Ürün görseli 01', 'important' => true],
                ['original_name' => 'urun-02.png', 'ext' => 'png', 'mime' => 'image/png', 'content' => 'Ürün görseli 02'],
                ['original_name' => 'urun-03.webp', 'ext' => 'webp', 'mime' => 'image/webp', 'content' => 'Ürün görseli 03'],
            ],
        ];

        $navigation = DriveStructure::navigation();

        foreach ($companies as $company) {
            $uploaderId = User::query()->where('company_id', $company->id)->value('id');
            $basePrefix = trim(str_replace('{company_id}', (string) $company->id, config('drive.path_prefix', 'companies/{company_id}/drive')), '/');

            foreach ($navigation as $module) {
                $moduleSlug = $module['module'];
                foreach ($module['folders'] as $folder) {
                    $folderKey = $folder['key'];
                    if (! isset($definitions[$folderKey])) {
                        continue;
                    }

                    foreach ($definitions[$folderKey] as $file) {
                        $uuid = (string) Str::uuid();
                        $folderPath = trim($basePrefix . '/' . $moduleSlug . '/' . $folderKey . '/' . $uuid, '/');
                        $path = $folderPath . '/content';

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
                                'uuid' => $uuid,
                            ],
                            [
                                'disk' => $disk,
                                'module' => $moduleSlug,
                                'category' => $folderKey,
                                'path' => $path,
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
}
