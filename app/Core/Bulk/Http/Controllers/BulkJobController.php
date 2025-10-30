<?php

namespace App\Core\Bulk\Http\Controllers;

use App\Core\Bulk\BulkActionService;
use App\Core\Bulk\Models\BulkJob;
use App\Core\ConsoleKit\ConsoleController;
use App\Modules\Inventory\Domain\Models\StockMovement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use RuntimeException;

/**
 * Bulk işlerin durumunu listeleyen ve yeni kayıtlar oluşturan uçlar.
 */
class BulkJobController extends ConsoleController
{
    public function __construct(private readonly BulkActionService $service)
    {
    }

    public function index(Request $request): JsonResponse
    {
        Gate::authorize('move', StockMovement::class);

        $companyId = currentCompanyId();
        if (! $companyId) {
            throw new RuntimeException('Şirket kimliği bulunamadı.');
        }

        $jobs = $this->service->latestForUser($companyId, $request->user());

        $payload = collect($jobs)->map(function (BulkJob $job) {
            return [
                'id' => $job->id,
                'module' => $job->module,
                'action' => $job->action,
                'status' => $job->status,
                'progress' => $job->progress,
                'items_total' => $job->items_total,
                'items_done' => $job->items_done,
                'error' => $job->error,
                'started_at' => optional($job->started_at)?->toIso8601String(),
                'finished_at' => optional($job->finished_at)?->toIso8601String(),
            ];
        })->all();

        $timestamp = Carbon::now();

        return response()->json([
            'jobs' => $payload,
        ])->setEtag('W/"' . sha1(json_encode($payload)) . '"')
            ->setLastModified($timestamp)
            ->header('X-Freshness-Key', 'bulk-jobs:' . $companyId . ':' . ($request->user()?->getAuthIdentifier() ?? 'guest'))
            ->header('X-Freshness-Timestamp', $timestamp->toRfc7231String());
    }

    public function store(Request $request): JsonResponse
    {
        $companyId = currentCompanyId();
        if (! $companyId) {
            throw new RuntimeException('Şirket kimliği bulunamadı.');
        }

        Gate::authorize('move', StockMovement::class);

        $validated = $request->validate([
            'module' => ['required', 'string'],
            'action' => ['required', 'string'],
            'items' => ['array'],
            'items.*.id' => ['required', 'integer'],
            'items.*.qty' => ['numeric'],
            'params' => ['array'],
        ]);

        $job = $this->service->dispatch(
            $companyId,
            $request->user(),
            $validated['module'],
            $validated['action'],
            [
                'items' => $validated['items'] ?? [],
                'params' => $validated['params'] ?? [],
                'items_total' => count($validated['items'] ?? []),
            ]
        );

        return response()->json([
            'job_id' => $job->id,
            'status' => $job->status,
            'message' => 'Toplu işlem kuyruğa alındı.',
        ], 202);
    }
}
