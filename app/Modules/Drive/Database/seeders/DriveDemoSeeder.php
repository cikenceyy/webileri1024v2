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

        $disk = config('drive.disk', config('filesystems.default', 'local'));
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
                        $identifier = (string) Str::ulid();
                        $extension = strtolower($file['ext']);
                        $nameSlug = Str::of(pathinfo($file['original_name'], PATHINFO_FILENAME) ?: 'file')
                            ->slug('-')
                            ->limit(60, '')
                            ->lower()
                            ->whenEmpty(fn () => 'file')
                            ->value();
                        $today = now();
                        $directory = implode('/', array_filter([
                            $basePrefix,
                            $moduleSlug,
                            $folderKey,
                            $today->format('Y'),
                            $today->format('m'),
                            $today->format('d'),
                        ]));
                        $filename = sprintf('%s_%s.%s', $identifier, $nameSlug, $extension);
                        $path = trim($directory . '/' . $filename, '/');
                        $content = (string) ($file['content'] ?? 'demo');

                        try {
                            $storage->put($path, $content, ['visibility' => 'private']);
                        } catch (\Throwable $exception) {
                            report($exception);
                            continue;
                        }

                        $size = $storage->exists($path) ? ($storage->size($path) ?: strlen($content)) : strlen($content);
                        $hash = hash('sha256', $content);

                        $media = Media::query()->firstOrNew([
                            'company_id' => $company->id,
                            'module' => $moduleSlug,
                            'category' => $folderKey,
                            'original_name' => $file['original_name'],
                        ]);

                        $media->fill([
                            'disk' => $disk,
                            'visibility' => 'private',
                            'path' => $path,
                            'mime' => strtolower($file['mime']),
                            'ext' => $extension,
                            'size' => $size,
                            'sha256' => $hash,
                            'width' => null,
                            'height' => null,
                            'thumb_path' => null,
                            'is_important' => (bool) ($file['important'] ?? false),
                            'uploaded_by' => $uploaderId,
                        ]);

                        if (! $media->exists) {
                            $media->company_id = $company->id;
                            $media->module = $moduleSlug;
                            $media->category = $folderKey;
                            $media->original_name = $file['original_name'];
                        }

                        $media->save();
                    }
                }
            }
        }
    }
}
