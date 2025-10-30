<?php

namespace App\Core\Reports\Http\Controllers;

use App\Core\Reports\Models\ReportSnapshot;
use App\Core\Reports\ReportRegistry;
use App\Core\Reports\ReportService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Rapor merkezi ekranı ve snapshot indirme uçları.
 */
class ReportController
{
    public function __construct(private readonly ReportRegistry $registry, private readonly ReportService $service)
    {
    }

    public function index(Request $request): View
    {
        Gate::authorize('viewReports');

        $companyId = currentCompanyId();
        if (! $companyId) {
            throw new RuntimeException('Şirket kimliği çözümlenemedi.');
        }

        $snapshots = ReportSnapshot::query()
            ->where('company_id', $companyId)
            ->latest('updated_at')
            ->get();

        return view('admin.reports.index', [
            'definitions' => $this->registry->all(),
            'snapshots' => $snapshots,
            'pollInterval' => config('consolekit.polling_interval_seconds'),
        ]);
    }

    public function list(Request $request): JsonResponse
    {
        Gate::authorize('viewReports');

        $companyId = currentCompanyId();
        if (! $companyId) {
            throw new RuntimeException('Şirket kimliği çözümlenemedi.');
        }

        $snapshots = ReportSnapshot::query()
            ->where('company_id', $companyId)
            ->orderByDesc('updated_at')
            ->get()
            ->map(function (ReportSnapshot $snapshot) {
                return [
                    'id' => $snapshot->id,
                    'report_key' => $snapshot->report_key,
                    'status' => $snapshot->status,
                    'rows' => $snapshot->rows,
                    'generated_at' => optional($snapshot->generated_at)?->toIso8601String(),
                    'valid_until' => optional($snapshot->valid_until)?->toIso8601String(),
                    'updated_at' => optional($snapshot->updated_at)?->toIso8601String(),
                    'storage_path' => $snapshot->storage_path,
                    'meta' => $snapshot->meta,
                ];
            })->all();

        $lastUpdated = collect($snapshots)
            ->map(fn ($snapshot) => $snapshot['generated_at'] ?? $snapshot['updated_at'] ?? null)
            ->filter()
            ->map(fn ($value) => Carbon::parse($value))
            ->max() ?? Carbon::now();

        return response()->json([
            'snapshots' => $snapshots,
        ])->setEtag('W/"' . sha1(json_encode($snapshots)) . '"')
            ->setLastModified($lastUpdated)
            ->header('X-Freshness-Key', 'reports:list:' . $companyId)
            ->header('X-Freshness-Timestamp', $lastUpdated->toRfc7231String());
    }

    public function refresh(Request $request, string $reportKey): JsonResponse
    {
        Gate::authorize('viewReports');

        $companyId = currentCompanyId();
        if (! $companyId) {
            throw new RuntimeException('Şirket kimliği çözümlenemedi.');
        }

        $this->registry->find($reportKey);
        $snapshot = $this->service->dispatch($companyId, $reportKey, []);

        return response()->json([
            'snapshot_id' => $snapshot->id,
            'status' => $snapshot->status,
            'message' => 'Rapor yenileme kuyruğa alındı.',
        ], 202);
    }

    public function download(Request $request, ReportSnapshot $snapshot)
    {
        Gate::authorize('viewReports');

        $companyId = currentCompanyId();
        if (! $companyId || $snapshot->company_id !== $companyId) {
            abort(404);
        }

        if ($snapshot->status !== 'ready') {
            abort(409, 'Rapor hazır değil.');
        }

        $disk = Storage::disk('local');
        if (! $snapshot->storage_path || ! $disk->exists($snapshot->storage_path)) {
            abort(404);
        }

        $content = $disk->get($snapshot->storage_path);
        $etag = 'W/"' . sha1($content) . '"';
        $timestamp = optional($snapshot->generated_at)?->toDateTime() ?? Carbon::now();
        $timestampString = $timestamp instanceof Carbon ? $timestamp->toRfc7231String() : Carbon::instance($timestamp)->toRfc7231String();

        $freshKey = 'reports:download:' . $snapshot->id;

        if ($request->headers->get('If-None-Match') === $etag) {
            return response('', 304)
                ->setEtag($etag)
                ->setLastModified($timestamp)
                ->header('X-Freshness-Key', $freshKey)
                ->header('X-Freshness-Timestamp', $timestampString);
        }

        return response($content, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="' . Str::slug($snapshot->report_key) . '.json"',
        ])->setEtag($etag)
            ->setLastModified($timestamp)
            ->header('X-Freshness-Key', $freshKey)
            ->header('X-Freshness-Timestamp', $timestampString);
    }
}
