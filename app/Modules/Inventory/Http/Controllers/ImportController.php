<?php

namespace App\Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Inventory\Domain\Models\Product;
use App\Modules\Inventory\Domain\Models\ProductCategory;
use App\Modules\Inventory\Domain\Models\Unit;
use App\Modules\Inventory\Http\Requests\ImportProductsRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ImportController extends Controller
{
    public function form(): View
    {
        $this->authorize('viewAny', Product::class);
        $this->authorize('create', Product::class);

        return view('inventory::import.products');
    }

    public function sample(): StreamedResponse
    {
        $this->authorize('viewAny', Product::class);
        $this->authorize('create', Product::class);

        $headers = ['sku', 'name', 'category_code', 'price', 'unit', 'barcode', 'reorder_point', 'status'];
        $sample = ['PRD-001', 'Örnek Ürün', 'demo-category', '199.90', 'pcs', '1234567890123', '5', 'active'];

        return response()->streamDownload(function () use ($headers, $sample) {
            $handle = fopen('php://output', 'w');

            if ($handle === false) {
                return;
            }

            fputcsv($handle, $headers);
            fputcsv($handle, $sample);
            fclose($handle);
        }, 'inventory-products-sample.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function store(ImportProductsRequest $request): RedirectResponse
    {
        $this->authorize('create', Product::class);
        $companyId = $this->companyIdOrFail($request);

        $path = $request->file('file')->store('imports');
        $fullPath = Storage::path($path);
        $handle = fopen($fullPath, 'r');

        if (! $handle) {
            throw ValidationException::withMessages(['file' => 'Dosya açılamadı.']);
        }

        $header = fgetcsv($handle);
        if (! $header) {
            fclose($handle);
            Storage::delete($path);
            throw ValidationException::withMessages(['file' => 'CSV içeriği boş.']);
        }

        $map = $this->mapHeader($header);
        $required = ['sku', 'name'];
        foreach ($required as $column) {
            if (! isset($map[$column])) {
                fclose($handle);
                Storage::delete($path);
                throw ValidationException::withMessages(['file' => "Eksik sütun: {$column}"]);
            }
        }

        $limit = 5000;
        $success = 0;
        $failed = 0;
        $errors = [];

        while (($row = fgetcsv($handle)) !== false && $limit > 0) {
            $limit--;
            $payload = $this->rowToPayload($row, $map);

            if (! $payload['sku'] || ! $payload['name']) {
                $failed++;
                $errors[] = 'Boş SKU veya isim';
                continue;
            }

            try {
                $this->importRow($companyId, $payload);
                $success++;
            } catch (\Throwable $e) {
                $failed++;
                $errors[] = $e->getMessage();
            }
        }

        fclose($handle);
        Storage::delete($path);

        return redirect()->route('admin.inventory.import.products.form')->with('status', "{$success} satır başarıyla içe aktarıldı, {$failed} satır atlandı.")
            ->with('import_errors', collect($errors)->take(5)->toArray());
    }

    protected function mapHeader(array $header): array
    {
        $map = [];
        foreach ($header as $index => $value) {
            $key = Str::of($value)->lower()->trim()->toString();
            $map[$key] = $index;
        }

        return $map;
    }

    protected function rowToPayload(array $row, array $map): array
    {
        $get = function (string $column) use ($row, $map) {
            if (! isset($map[$column])) {
                return null;
            }

            return trim($row[$map[$column]] ?? '');
        };

        return [
            'sku' => $get('sku'),
            'name' => $get('name'),
            'category_code' => $get('category_code'),
            'price' => $get('price'),
            'unit_code' => $get('unit'),
            'barcode' => $get('barcode'),
            'reorder_point' => $get('reorder_point'),
            'status' => $get('status') ?: 'active',
        ];
    }

    protected function importRow(int $companyId, array $payload): void
    {
        $categoryId = $this->resolveCategory($companyId, $payload['category_code'] ?? null);
        $unit = $this->resolveUnit($companyId, $payload['unit_code'] ?? null);

        Product::updateOrCreate([
            'company_id' => $companyId,
            'sku' => $payload['sku'],
        ], [
            'name' => $payload['name'],
            'category_id' => $categoryId,
            'price' => $payload['price'] !== '' ? (float) $payload['price'] : 0,
            'unit' => $unit['code'],
            'base_unit_id' => $unit['id'],
            'barcode' => $payload['barcode'] ?: null,
            'reorder_point' => $payload['reorder_point'] !== '' ? (float) $payload['reorder_point'] : 0,
            'status' => in_array($payload['status'], ['active', 'inactive'], true) ? $payload['status'] : 'active',
        ]);
    }

    protected function resolveCategory(int $companyId, ?string $code): ?int
    {
        if (! $code) {
            return null;
        }

        $category = ProductCategory::firstOrCreate([
            'company_id' => $companyId,
            'code' => $code,
        ], [
            'name' => Str::title(str_replace(['_', '-'], ' ', $code)),
            'status' => 'active',
        ]);

        return $category->id;
    }

    protected function resolveUnit(int $companyId, ?string $code): array
    {
        $code = $code ?: config('inventory.default_unit', 'pcs');

        $unit = Unit::firstOrCreate([
            'company_id' => $companyId,
            'code' => $code,
        ], [
            'name' => strtoupper($code),
            'is_base' => false,
            'to_base' => 1,
        ]);

        return ['id' => $unit->id, 'code' => $unit->code];
    }

    protected function companyIdOrFail(Request $request): int
    {
        $companyId = $request->attributes->get('company_id') ?? (app()->bound('company') ? app('company')->id : null);

        if (! $companyId) {
            throw ValidationException::withMessages([
                'company_id' => 'Şirket bağlamı bulunamadı.',
            ]);
        }

        return $companyId;
    }
}
