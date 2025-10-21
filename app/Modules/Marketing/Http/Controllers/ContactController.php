<?php

namespace App\Modules\Marketing\Http\Controllers;

use App\Modules\Marketing\Domain\Models\Customer;
use App\Modules\Marketing\Domain\Models\CustomerContact;
use App\Modules\Marketing\Http\Requests\StoreContactRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ContactController extends \App\Http\Controllers\Controller
{
    public function index(Customer $customer): View
    {
        $this->authorize('view', $customer);

        return view('marketing::contacts.index', [
            'customer' => $customer->load('contacts'),
        ]);
    }

    public function store(StoreContactRequest $request, Customer $customer): RedirectResponse
    {
        $data = $request->validated();
        $data['company_id'] = currentCompanyId();
        $data['is_primary'] = (bool) ($data['is_primary'] ?? false);

        if ($data['is_primary']) {
            $customer->contacts()->update(['is_primary' => false]);
        }

        $customer->contacts()->create($data);

        return back()->with('status', __('Contact saved.'));
    }

    public function update(StoreContactRequest $request, CustomerContact $contact): RedirectResponse
    {
        $this->authorize('update', $contact);

        $data = $request->validated();
        $data['is_primary'] = (bool) ($data['is_primary'] ?? false);

        if ($data['is_primary']) {
            $contact->customer->contacts()->update(['is_primary' => false]);
        }

        $contact->update($data);

        return back()->with('status', __('Contact updated.'));
    }

    public function destroy(CustomerContact $contact): RedirectResponse
    {
        $this->authorize('delete', $contact);

        $contact->delete();

        return back()->with('status', __('Contact removed.'));
    }
}
