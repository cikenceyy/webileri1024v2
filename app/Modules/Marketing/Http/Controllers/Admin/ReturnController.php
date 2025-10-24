<?php

namespace App\Modules\Marketing\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Inventory\Domain\Models\Product;
use App\Modules\Marketing\Domain\Models\Customer;
use App\Modules\Marketing\Domain\Models\ReturnRequest;
use App\Modules\Marketing\Http\Requests\Admin\ApproveReturnRequest;
use App\Modules\Marketing\Http\Requests\Admin\CloseReturnRequest;
use App\Modules\Marketing\Http\Requests\Admin\StoreReturnRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReturnController extends Controller
{
    public function index(Request $request): View
    {
        $this->abortIfDisabled();
        $this->authorize('viewAny', ReturnRequest::class);

        $query = ReturnRequest::query()->with('customer')->latest();
        $search = trim((string) $request->query('q', ''));
        $status = $request->query('status');

        if ($search !== '') {
            $query->search($search);
        }

        if ($status && in_array($status, ReturnRequest::statuses(), true)) {
            $query->where('status', $status);
        }

        /** @var LengthAwarePaginator $returns */
        $returns = $query->paginate()->withQueryString();

        return view('marketing::admin.returns.index', [
            'returns' => $returns,
            'filters' => [
                'q' => $search,
                'status' => $status,
            ],
        ]);
    }

    public function create(): View
    {
        $this->abortIfDisabled();
        $this->authorize('create', ReturnRequest::class);

        return view('marketing::admin.returns.create', [
            'customers' => $this->customers(),
            'products' => $this->products(),
        ]);
    }

    public function store(StoreReturnRequest $request): RedirectResponse
    {
        $this->abortIfDisabled();
        $this->authorize('create', ReturnRequest::class);

        DB::transaction(function () use ($request): void {
            $data = $request->validated();

            $return = ReturnRequest::create([
                'company_id' => currentCompanyId(),
                'customer_id' => $data['customer_id'],
                'related_order_id' => $data['related_order_id'] ?? null,
                'status' => ReturnRequest::STATUS_OPEN,
                'reason' => $data['reason'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($data['lines'] as $line) {
                $return->lines()->create([
                    'company_id' => $return->company_id,
                    'product_id' => $line['product_id'],
                    'variant_id' => $line['variant_id'] ?? null,
                    'qty' => $line['qty'],
                    'reason_code' => $line['reason_code'] ?? null,
                    'notes' => $line['notes'] ?? null,
                ]);
            }
        });

        return redirect()
            ->route('admin.marketing.returns.index')
            ->with('status', __('İade talebi oluşturuldu.'));
    }

    public function show(ReturnRequest $return): View
    {
        $this->abortIfDisabled();
        $this->authorize('view', $return);

        $return->load(['customer', 'order', 'lines.product']);

        return view('marketing::admin.returns.show', [
            'return' => $return,
        ]);
    }

    public function approve(ApproveReturnRequest $request, ReturnRequest $return): RedirectResponse
    {
        $this->abortIfDisabled();
        $this->authorize('approve', $return);

        $return->update(['status' => ReturnRequest::STATUS_APPROVED]);

        return redirect()
            ->route('admin.marketing.returns.show', $return)
            ->with('status', __('İade talebi onaylandı.'));
    }

    public function close(CloseReturnRequest $request, ReturnRequest $return): RedirectResponse
    {
        $this->abortIfDisabled();
        $this->authorize('close', $return);

        $return->update(['status' => ReturnRequest::STATUS_CLOSED]);

        return redirect()
            ->route('admin.marketing.returns.show', $return)
            ->with('status', __('İade talebi kapatıldı.'));
    }

    protected function customers()
    {
        return Customer::query()
            ->where('company_id', currentCompanyId())
            ->orderBy('name')
            ->pluck('name', 'id');
    }

    protected function products()
    {
        return Product::query()
            ->where('company_id', currentCompanyId())
            ->orderBy('name')
            ->pluck('name', 'id');
    }

    protected function abortIfDisabled(): void
    {
        if (! config('features.marketing.returns', true)) {
            abort(404);
        }
    }
}
