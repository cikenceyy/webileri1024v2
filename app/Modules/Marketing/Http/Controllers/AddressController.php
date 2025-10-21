<?php

namespace App\Modules\Marketing\Http\Controllers;

use App\Modules\Marketing\Domain\Models\Customer;
use App\Modules\Marketing\Domain\Models\CustomerAddress;
use App\Modules\Marketing\Http\Requests\StoreAddressRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AddressController extends \App\Http\Controllers\Controller
{
    public function index(Customer $customer): View
    {
        $this->authorize('view', $customer);

        return view('marketing::addresses.index', [
            'customer' => $customer->load('addresses'),
        ]);
    }

    public function store(StoreAddressRequest $request, Customer $customer): RedirectResponse
    {
        $data = $request->validated();
        $data['company_id'] = currentCompanyId();
        $data['is_primary'] = (bool) ($data['is_primary'] ?? false);

        if ($data['is_primary']) {
            $customer->addresses()
                ->where('type', $data['type'])
                ->update(['is_primary' => false]);
        }

        $customer->addresses()->create($data);

        return back()->with('status', __('Address saved.'));
    }

    public function update(StoreAddressRequest $request, CustomerAddress $address): RedirectResponse
    {
        $this->authorize('update', $address);

        $data = $request->validated();
        $data['is_primary'] = (bool) ($data['is_primary'] ?? false);

        if ($data['is_primary']) {
            $address->customer->addresses()
                ->where('type', $data['type'])
                ->update(['is_primary' => false]);
        }

        $address->update($data);

        return back()->with('status', __('Address updated.'));
    }

    public function destroy(CustomerAddress $address): RedirectResponse
    {
        $this->authorize('delete', $address);

        $address->delete();

        return back()->with('status', __('Address removed.'));
    }
}
