<?php

namespace App\Core\Exports\Http\Controllers;

use App\Core\Exports\Jobs\ExportTableJob;
use App\Core\Exports\Models\TableExport;
use App\Core\TableKit\QueryAdapter;
use App\Core\TableKit\TableExporterRegistry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * TableKit export işlemlerini yöneten controller sınıfı.
 * Amaç: Export oluşturma, listeleme ve indirme uçlarını sağlamak.
 */
class ExportController extends Controller
{
    private const RATE_LIMIT_KEY = 'tablekit-exports';

    public function __construct()
    {
        $this->middleware('throttle:tablekit-export')->only('store');
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        $companyId = currentCompanyId();

        $exports = TableExport::query()
            ->where('company_id', $companyId)
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        return view('admin.exports.index', ['exports' => $exports]);
    }

    public function store(Request $request, string $tableKey, TableExporterRegistry $registry): JsonResponse
    {
        $user = $request->user();
        $companyId = currentCompanyId();

        $limitKey = self::RATE_LIMIT_KEY . ':' . $companyId . ':' . $user->id;
        if (RateLimiter::tooManyAttempts($limitKey, 2)) {
            abort(429, __('Aynı anda en fazla iki export isteği gönderebilirsiniz.'));
        }

        $data = $request->validate([
            'format' => ['required', 'in:csv,xlsx'],
            'state' => ['nullable', 'array'],
            'query' => ['nullable', 'string'],
        ]);

        $state = $data['state'] ?? null;

        try {
            $resolver = $registry->resolve($tableKey);
        } catch (InvalidArgumentException $exception) {
            abort(404, __('Export tanımı bulunamadı.'));
        }

        RateLimiter::hit($limitKey, 60);

        $definition = app()->call($resolver);

        if (! is_array($definition) || ! isset($definition['builder'], $definition['configure'])) {
            abort(500, __('Export yapılandırması eksik.'));
        }

        if ($state === null && isset($data['query'])) {
            $queryString = ltrim((string) $data['query'], '?');
            parse_str($queryString, $queryParams);

            $builder = $definition['builder'];
            $configure = $definition['configure'];
            /** @var QueryAdapter $adapter */
            $adapter = $configure(QueryAdapter::make($builder, $tableKey));
            $fakeRequest = Request::create('/', 'GET', $queryParams);
            $state = $adapter->previewState($fakeRequest);
        }

        $export = TableExport::query()->create([
            'company_id' => $companyId,
            'user_id' => $user->id,
            'table_key' => $tableKey,
            'format' => $data['format'],
            'status' => TableExport::STATUS_PENDING,
            'params' => [
                'state' => $state,
                'query' => $data['query'] ?? null,
            ],
            'retention_until' => now()->addDays(14),
        ]);

        Bus::dispatch(new ExportTableJob($export->id));

        return response()->json(['id' => $export->id]);
    }

    public function download(Request $request, TableExport $export): BinaryFileResponse
    {
        $user = $request->user();
        $companyId = currentCompanyId();

        abort_unless($export->company_id === $companyId && $export->user_id === $user->id, 403);
        abort_unless($export->status === TableExport::STATUS_DONE && $export->file_path, 404);

        $disk = Storage::disk('local');
        abort_unless($disk->exists($export->file_path), 404);

        return response()->download($disk->path($export->file_path));
    }

    public function destroy(Request $request, TableExport $export): JsonResponse
    {
        $user = $request->user();
        $companyId = currentCompanyId();

        abort_unless($export->company_id === $companyId && $export->user_id === $user->id, 403);

        if ($export->file_path) {
            Storage::disk('local')->delete($export->file_path);
        }

        $export->delete();

        return response()->json(['status' => 'ok']);
    }
}
