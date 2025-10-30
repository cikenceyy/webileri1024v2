<?php

namespace App\Core\TableKit\Http\Controllers;

use App\Core\TableKit\Models\TablekitFilter;
use App\Core\TableKit\Repositories\SavedFilterRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * TableKit kaydedilmiş filtre işlemleri için JSON tabanlı controller.
 * Amaç: Kullanıcıların filtre kaydetme, listeleme ve varsayılan atamasını sağlamaktır.
 */
class SavedFilterController extends Controller
{
    public function __construct(private readonly SavedFilterRepository $repository)
    {
    }

    public function index(Request $request, string $tableKey): JsonResponse
    {
        $user = $request->user();
        $companyId = currentCompanyId();

        $filters = TablekitFilter::query()
            ->where('company_id', $companyId)
            ->where('user_id', $user->id)
            ->where('table_key', $tableKey)
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();

        return response()->json([
            'filters' => $filters->map(fn (TablekitFilter $filter) => [
                'id' => $filter->id,
                'name' => $filter->name,
                'payload' => $filter->payload,
                'is_default' => $filter->is_default,
            ]),
        ]);
    }

    public function store(Request $request, string $tableKey): JsonResponse
    {
        $user = $request->user();
        $companyId = currentCompanyId();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'payload' => ['required', 'array'],
            'is_default' => ['sometimes', 'boolean'],
        ]);

        $filter = $this->repository->create(
            $companyId,
            $user->id,
            $tableKey,
            $data['name'],
            $data['payload'],
            (bool) ($data['is_default'] ?? false),
        );

        return response()->json([
            'id' => $filter->id,
            'name' => $filter->name,
            'payload' => $filter->payload,
            'is_default' => $filter->is_default,
        ], 201);
    }

    public function destroy(Request $request, string $tableKey, TablekitFilter $filter): JsonResponse
    {
        $user = $request->user();
        $companyId = currentCompanyId();

        abort_unless($filter->company_id === $companyId && $filter->user_id === $user->id && $filter->table_key === $tableKey, 403);

        $filter->delete();

        return response()->json(['status' => 'ok']);
    }

    public function makeDefault(Request $request, string $tableKey, TablekitFilter $filter): JsonResponse
    {
        $user = $request->user();
        $companyId = currentCompanyId();

        abort_unless($filter->company_id === $companyId && $filter->user_id === $user->id && $filter->table_key === $tableKey, 403);

        $this->repository->markDefault($filter);

        return response()->json(['status' => 'ok']);
    }
}
