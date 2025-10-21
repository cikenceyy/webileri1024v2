<?php

namespace App\Modules\Marketing\Http\Controllers;

use App\Modules\Marketing\Domain\Models\Attachment;
use App\Modules\Marketing\Domain\Models\Customer;
use App\Modules\Marketing\Domain\Models\CustomerAddress;
use App\Modules\Marketing\Domain\Models\CustomerContact;
use App\Modules\Marketing\Domain\Models\Note;
use App\Modules\Marketing\Http\Requests\StoreCustomerRequest;
use App\Modules\Marketing\Http\Requests\UpdateCustomerRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends \App\Http\Controllers\Controller
{
    protected array $sortable = ['name', 'code', 'created_at'];

    public function __construct()
    {
        $this->authorizeResource(Customer::class, 'customer');
    }

    public function index(Request $request): View
    {
        $query = Customer::query();

        $search = trim((string) $request->query('q', ''));
        $status = (string) $request->query('status', '');
        $sort = strtolower((string) $request->query('sort', ''));
        $directionParam = strtolower((string) $request->query('dir', 'desc'));
        $direction = $directionParam === 'asc' ? 'asc' : 'desc';

        if ($search !== '') {
            $query->search($search);
        }

        if ($status !== '' && in_array($status, ['active', 'inactive'], true)) {
            $query->where('status', $status);
        }

        $sortKey = in_array($sort, $this->sortable, true) ? $sort : 'created_at';
        $query->orderBy($sortKey, $direction);

        /** @var LengthAwarePaginator $customers */
        $customers = $query->paginate(15)->withQueryString();

        return view('marketing::customers.index', [
            'customers' => $customers,
            'filters' => [
                'q' => $search,
                'status' => $status,
                'sort' => $sortKey,
                'dir' => $direction,
            ],
        ]);
    }

    public function create(): View
    {
        return view('marketing::customers.create');
    }

    public function store(StoreCustomerRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['company_id'] = currentCompanyId();
        $data['status'] = $data['status'] ?? 'active';

        Customer::create($data);

        return redirect()
            ->route('admin.marketing.customers.index')
            ->with('status', __('Customer created successfully.'));
    }

    public function show(Customer $customer): View
    {
        $customer->load(['contacts', 'addresses', 'quotes' => function ($query): void {
            $query->latest()->limit(5);
        }, 'orders' => function ($query): void {
            $query->latest()->limit(5);
        }]);

        $activities = $customer->activities()->latest()->limit(10)->get();
        $notes = $customer->notes()->latest()->limit(10)->get();
        $attachments = $customer->attachments()->with('media')->latest()->limit(10)->get();

        return view('marketing::customers.show', [
            'customer' => $customer,
            'activities' => $activities,
            'notes' => $notes,
            'attachments' => $attachments,
        ]);
    }

    public function edit(Customer $customer): View
    {
        return view('marketing::customers.edit', [
            'customer' => $customer,
        ]);
    }

    public function update(UpdateCustomerRequest $request, Customer $customer): RedirectResponse
    {
        $customer->update($request->validated());

        return redirect()
            ->route('admin.marketing.customers.show', $customer)
            ->with('status', __('Customer updated successfully.'));
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        $customer->delete();

        return redirect()
            ->route('admin.marketing.customers.index')
            ->with('status', __('Customer removed successfully.'));
    }

    protected function resetPrimaryContact(Customer $customer): void
    {
        if ($customer->contacts()->where('is_primary', true)->count() > 1) {
            /** @var CustomerContact|null $first */
            $first = $customer->contacts()->where('is_primary', true)->oldest()->first();
            if ($first) {
                $customer->contacts()
                    ->where('id', '!=', $first->id)
                    ->update(['is_primary' => false]);
            }
        }
    }

    protected function resetPrimaryAddress(Customer $customer, string $type): void
    {
        $primaryAddresses = $customer->addresses()->where('type', $type)->where('is_primary', true);
        if ($primaryAddresses->count() > 1) {
            /** @var CustomerAddress|null $first */
            $first = $primaryAddresses->oldest()->first();
            if ($first) {
                $customer->addresses()
                    ->where('type', $type)
                    ->where('id', '!=', $first->id)
                    ->update(['is_primary' => false]);
            }
        }
    }
}
