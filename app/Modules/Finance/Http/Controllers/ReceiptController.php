<?php

namespace App\Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Marketing\Domain\Models\Customer;
use App\Modules\Finance\Domain\Models\Receipt;
use App\Modules\Finance\Http\Requests\StoreReceiptRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReceiptController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Receipt::class, 'receipt', ['except' => ['store']]);
    }

    public function index(Request $request): View
    {
        $query = Receipt::query()->with('customer', 'bankAccount');

        if ($search = $request->string('q')->trim()) {
            $query->where(function ($q) use ($search): void {
                $like = '%' . $search . '%';
                $q->where('receipt_no', 'like', $like)
                    ->orWhereHas('customer', static function ($customer) use ($like): void {
                        $customer->where('name', 'like', $like);
                    });
            });
        }

        if ($customerId = $request->integer('customer_id')) {
            $query->where('customer_id', $customerId);
        }

        $receipts = $query->latest('receipt_date')->paginate(15)->withQueryString();

        return view('finance::receipts.index', [
            'receipts' => $receipts,
            'filters' => $request->only(['q', 'customer_id']),
            'customers' => Customer::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Receipt::class);

        return view('finance::receipts.create', $this->formData());
    }

    public function store(StoreReceiptRequest $request): RedirectResponse
    {
        $this->authorize('create', Receipt::class);

        $data = $request->validated();
        $data['receipt_no'] = $data['receipt_no'] ?? $this->generateReceiptNo();
        $data['created_by'] = optional(auth()->user())->id;

        $receipt = Receipt::create($data);

        return redirect()->route('admin.finance.receipts.show', $receipt)->with('status', __('Tahsilat kaydedildi.'));
    }

    public function show(Receipt $receipt): View
    {
        $receipt->load(['customer', 'bankAccount', 'allocations.invoice']);

        return view('finance::receipts.show', [
            'receipt' => $receipt,
        ]);
    }

    protected function formData(): array
    {
        $companyId = currentCompanyId();

        return [
            'customers' => Customer::where('company_id', $companyId)->orderBy('name')->get(['id', 'name']),
            'bankAccounts' => \App\Modules\Finance\Domain\Models\BankAccount::where('company_id', $companyId)->orderBy('name')->get(['id', 'name']),
            'currencies' => config('finance.supported_currencies'),
        ];
    }

    protected function generateReceiptNo(): string
    {
        $prefix = 'RCPT-' . now()->format('Ym') . '-';

        do {
            $candidate = $prefix . str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (Receipt::where('company_id', currentCompanyId())->where('receipt_no', $candidate)->exists());

        return $candidate;
    }
}
