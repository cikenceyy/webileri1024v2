<?php

namespace App\Modules\Inventory\Http\Controllers;

use App\Core\Bulk\BulkActionService;
use App\Core\ConsoleKit\CommandPalette;
use App\Core\ConsoleKit\ConsoleController;
use App\Core\ConsoleKit\ConsoleGrid;
use App\Core\ConsoleKit\QuickFilters;
use App\Modules\Inventory\Domain\Models\Product;
use App\Modules\Inventory\Domain\Models\StockMovement;
use App\Modules\Inventory\Domain\Models\Warehouse;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use RuntimeException;

/**
 * Stok konsolunu ConsoleKit ile sunar, gelişmiş arama + bulk işlemleri destekler.
 */
class StockConsoleController extends ConsoleController
{
    public function __construct(private readonly BulkActionService $bulkActions)
    {
    }

    /**
     * Konsol ekranı: kolon, komut ve hızlı filtre konfigurasyonunu döndürür.
     */
    public function index(Request $request): View
    {
        $this->authorize('move', StockMovement::class);

        $companyId = currentCompanyId();
        if (! $companyId) {
            throw new RuntimeException('Şirket kimliği çözümlenemedi.');
        }

        $columns = $this->columns();
        $grid = ConsoleGrid::make('inventory:stock-console', $columns);

        $commands = CommandPalette::make([
            [
                'id' => 'reserve',
                'label' => 'Stoğu rezerve et',
                'permission' => 'inventory.stock.reserve',
                'shortcut' => 'r',
                'action' => 'reserve',
            ],
            [
                'id' => 'release',
                'label' => 'Rezervi düş',
                'permission' => 'inventory.stock.reserve',
                'shortcut' => 'u',
                'action' => 'release',
            ],
            [
                'id' => 'adjust',
                'label' => 'Düzeltme hareketi oluştur',
                'permission' => 'inventory.stock.adjust',
                'shortcut' => 'a',
                'action' => 'adjust',
            ],
        ])->resolveFor($request->user());

        $quickFilters = QuickFilters::make([
            ['id' => 'positive', 'label' => 'Pozitif', 'payload' => ['filters' => ['availability' => 'positive']]],
            ['id' => 'negative', 'label' => 'Negatif', 'payload' => ['filters' => ['availability' => 'negative']]],
            ['id' => 'reserved', 'label' => 'Rezerve > 0', 'payload' => ['filters' => ['has_reserve' => 1]]],
        ])->all();

        $warehouses = Warehouse::query()
            ->where('company_id', $companyId)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        return view('inventory::stock.console', [
            'grid' => $grid,
            'commands' => $commands,
            'quickFilters' => $quickFilters,
            'warehouses' => $warehouses,
            'pollInterval' => config('consolekit.polling_interval_seconds'),
        ]);
    }

    /**
     * Grid veri kaynağı: cursor paginate + gelişmiş filtre.
     */
    public function grid(Request $request): JsonResponse
    {
        $this->authorize('move', StockMovement::class);

        $companyId = currentCompanyId();
        if (! $companyId) {
            throw new RuntimeException('Şirket kimliği çözümlenemedi.');
        }

        $builder = DB::query()
            ->from('stock_items as si')
            ->select([
                'si.id',
                'si.company_id',
                'si.warehouse_id',
                'si.product_id',
                'si.qty',
                'si.reserved_qty',
                'si.updated_at',
                'p.name as product_name',
                'p.sku as product_sku',
                'p.barcode as product_barcode',
                'w.name as warehouse_name',
                DB::raw('(si.qty - si.reserved_qty) as available_qty'),
            ])
            ->leftJoin('products as p', function ($join) use ($companyId): void {
                $join->on('p.id', '=', 'si.product_id')
                    ->where('p.company_id', '=', $companyId);
            })
            ->leftJoin('warehouses as w', function ($join) use ($companyId): void {
                $join->on('w.id', '=', 'si.warehouse_id')
                    ->where('w.company_id', '=', $companyId);
            })
            ->where('si.company_id', $companyId);

        $filters = $request->query('filters', []);

        $availability = Arr::get($filters, 'availability');
        if ($availability === 'positive') {
            $builder->whereRaw('(si.qty - si.reserved_qty) > 0');
        } elseif ($availability === 'negative') {
            $builder->whereRaw('(si.qty - si.reserved_qty) < 0');
        }

        if ((int) Arr::get($filters, 'has_reserve') === 1) {
            $builder->where('si.reserved_qty', '>', 0);
        }

        $search = $request->string('filter_text')->toString();
        if ($search !== '') {
            $builder->where(function ($query) use ($search): void {
                $query->where('p.name', 'like', '%' . $search . '%')
                    ->orWhere('p.sku', 'like', '%' . $search . '%')
                    ->orWhere('p.barcode', 'like', '%' . $search . '%');
            });
        }

        return $this->makeGridResponse($request, $builder, 'inventory:stock-console', $this->columns(), [
            'sortable' => ['product_name', 'warehouse_name', 'qty', 'reserved_qty', 'available_qty', 'updated_at'],
            'filters' => [
                'warehouse_id' => ['type' => 'int', 'column' => 'si.warehouse_id'],
                'updated_at' => ['type' => 'date', 'column' => 'si.updated_at'],
            ],
            'map' => function ($row): array {
                $row = (array) $row;
                $available = (float) $row['available_qty'];

                return [
                    'id' => $row['id'],
                    'product' => $row['product_name'],
                    'sku' => $row['product_sku'],
                    'warehouse' => $row['warehouse_name'],
                    'qty' => number_format((float) $row['qty'], 2, ',', '.'),
                    'reserved_qty' => number_format((float) $row['reserved_qty'], 2, ',', '.'),
                    'available_qty' => number_format($available, 2, ',', '.'),
                    'status' => $available < 0 ? 'negative' : ($available === 0.0 ? 'zero' : 'positive'),
                    'updated_at' => Carbon::parse($row['updated_at'])->diffForHumans(),
                ];
            },
        ]);
    }

    /**
     * Konsoldan gelen bulk talepleri kuyruğa aktarır.
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('move', StockMovement::class);

        $companyId = currentCompanyId();
        if (! $companyId) {
            throw new RuntimeException('Şirket kimliği çözümlenemedi.');
        }

        $validator = Validator::make($request->all(), [
            'action' => ['required', 'in:reserve,release,adjust'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'integer'],
            'items.*.qty' => ['numeric'],
        ], [], [
            'items.*.id' => 'stok satırı',
            'items.*.qty' => 'miktar',
        ]);

        $data = $validator->validate();

        $job = $this->bulkActions->dispatch(
            $companyId,
            $request->user(),
            'inventory',
            $data['action'],
            [
                'items' => $data['items'],
                'items_total' => count($data['items']),
            ]
        );

        return response()->json([
            'job_id' => $job->id,
            'status' => $job->status,
            'message' => 'Toplu işlem kuyruğa alındı.',
        ], 202);
    }

    /**
     * Barkod/SKU hızlı araması.
     */
    public function lookup(Request $request): JsonResponse
    {
        $this->authorize('move', StockMovement::class);

        $companyId = currentCompanyId();
        if (! $companyId) {
            throw new RuntimeException('Şirket kimliği çözümlenemedi.');
        }

        $term = $request->string('q')->toString();
        if ($term === '') {
            return response()->json(['results' => []]);
        }

        $products = Product::query()
            ->where('company_id', $companyId)
            ->where(function ($query) use ($term): void {
                $query->where('sku', 'like', '%' . $term . '%')
                    ->orWhere('barcode', 'like', '%' . $term . '%')
                    ->orWhere('name', 'like', '%' . $term . '%');
            })
            ->limit(10)
            ->get(['id', 'name', 'sku', 'barcode']);

        return response()->json([
            'results' => $products->map(function (Product $product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'barcode' => $product->barcode,
                ];
            }),
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function columns(): array
    {
        return [
            ['key' => 'product', 'label' => 'Ürün', 'sortable' => true, 'width' => '240px'],
            ['key' => 'sku', 'label' => 'SKU', 'sortable' => true, 'width' => '140px'],
            ['key' => 'warehouse', 'label' => 'Depo', 'sortable' => true, 'width' => '160px'],
            ['key' => 'qty', 'label' => 'Toplam', 'sortable' => true, 'class' => 'text-end', 'width' => '120px'],
            ['key' => 'reserved_qty', 'label' => 'Rezerve', 'sortable' => true, 'class' => 'text-end', 'width' => '120px'],
            ['key' => 'available_qty', 'label' => 'Kullanılabilir', 'sortable' => true, 'class' => 'text-end', 'width' => '140px'],
            ['key' => 'status', 'label' => 'Durum', 'width' => '120px'],
            ['key' => 'updated_at', 'label' => 'Son Güncelleme', 'sortable' => true, 'width' => '160px'],
        ];
    }
}
