<?php

namespace App\Modules\Marketing\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Inventory\Domain\Models\PriceList;
use App\Modules\Marketing\Domain\Models\Customer;
use App\Modules\Marketing\Http\Requests\Admin\StoreCustomerRequest;
use App\Modules\Marketing\Http\Requests\Admin\UpdateCustomerRequest;
use App\Modules\Marketing\Http\Resources\CustomerResource;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Core\Support\TableKit\Filters;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $this->authorize('viewAny', Customer::class);

        $query = Customer::query()->with('priceList')->latest();
        $search = trim((string) $request->query('q', ''));
        $statusFilters = Filters::multi($request, 'status');

        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $normalizedStatuses = collect($statusFilters)
            ->filter(fn ($value) => in_array($value, ['active', 'inactive'], true))
            ->values();

        if ($normalizedStatuses->count() === 1) {
            $query->where('is_active', $normalizedStatuses->first() === 'active');
        } elseif ($normalizedStatuses->count() === 2) {
            // Her iki durum da seçildiyse filtre uygulamaya gerek yok.
        } elseif ($normalizedStatuses->count() === 0) {
            $legacyStatus = $request->query('status');

            if (in_array($legacyStatus, ['active', 'inactive'], true)) {
                $query->where('is_active', $legacyStatus === 'active');
                $statusFilters = [$legacyStatus];
            }
        }

        if ($normalizedStatuses->count() > 1 && $normalizedStatuses->count() < 2) {
            // tek eleman hariç, mantıksal olarak yukarıda yakalanır; burada ek işlem yok.
        }

        /** @var LengthAwarePaginator $customers */
        $customers = $query->paginate()->withQueryString();

        if ($request->wantsJson()) {
            return CustomerResource::collection($customers);
        }

        return view('marketing::admin.customers.index', [
            'customers' => $customers,
            'filters' => [
                'q' => $search,
                'status' => $statusFilters,
            ],
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Customer::class);

        return view('marketing::admin.customers.create', [
            'priceLists' => $this->priceLists(),
        ]);
    }

    public function store(StoreCustomerRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['company_id'] = currentCompanyId();
        $data['code'] = $this->generateCustomerCode($data['name']);
        $data['status'] = ($data['is_active'] ?? true) ? 'active' : 'inactive';

        Customer::create($data);

        return redirect()
            ->route('admin.marketing.customers.index')
            ->with('status', __('Müşteri kaydı oluşturuldu.'));
    }

    public function show(Request $request, Customer $customer): View|JsonResponse
    {
        $this->authorize('view', $customer);

        if ($request->wantsJson()) {
            return CustomerResource::make($customer->load('priceList'));
        }

        return view('marketing::admin.customers.show', [
            'customer' => $customer->load('priceList'),
        ]);
    }

    public function edit(Customer $customer): View
    {
        $this->authorize('update', $customer);

        return view('marketing::admin.customers.edit', [
            'customer' => $customer,
            'priceLists' => $this->priceLists(),
        ]);
    }

    public function update(UpdateCustomerRequest $request, Customer $customer): RedirectResponse
    {
        $data = $request->validated();
        $data['status'] = ($data['is_active'] ?? true) ? 'active' : 'inactive';

        $customer->update($data);

        return redirect()
            ->route('admin.marketing.customers.show', $customer)
            ->with('status', __('Müşteri güncellendi.'));
    }

    protected function priceLists()
    {
        return PriceList::query()
            ->where('company_id', currentCompanyId())
            ->orderBy('name')
            ->pluck('name', 'id');
    }

    protected function generateCustomerCode(string $name): string
    {
        $base = Str::upper(Str::slug($name, ''));

        if ($base === '') {
            $base = 'CUST';
        }

        $base = Str::limit($base, 12, '');
        $code = $base;
        $suffix = 1;

        while (Customer::query()->where('code', $code)->exists()) {
            $code = $base . '-' . str_pad((string) $suffix, 2, '0', STR_PAD_LEFT);
            $suffix++;

            if ($suffix > 99) {
                $code = $base . '-' . Str::upper(Str::random(4));
                break;
            }
        }

        return $code;
    }
}
