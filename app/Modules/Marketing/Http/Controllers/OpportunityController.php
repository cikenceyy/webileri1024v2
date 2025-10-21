<?php

namespace App\Modules\Marketing\Http\Controllers;

use App\Modules\Marketing\Domain\Models\Customer;
use App\Modules\Marketing\Domain\Models\Opportunity;
use App\Modules\Marketing\Http\Requests\StoreOpportunityRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OpportunityController extends \App\Http\Controllers\Controller
{
    public function __construct()
    {
        $this->authorizeResource(Opportunity::class, 'opportunity');
    }

    public function index(Request $request): View
    {
        $query = Opportunity::query()->with('customer');

        if ($stage = $request->query('stage')) {
            $query->where('stage', $stage);
        }

        if ($customerId = $request->query('customer_id')) {
            $query->where('customer_id', $customerId);
        }

        /** @var LengthAwarePaginator $opportunities */
        $opportunities = $query->latest()->paginate(15)->withQueryString();

        return view('marketing::opportunities.index', [
            'opportunities' => $opportunities,
            'customers' => Customer::orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('marketing::opportunities.create', [
            'customers' => Customer::orderBy('name')->get(),
        ]);
    }

    public function store(StoreOpportunityRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['company_id'] = currentCompanyId();

        Opportunity::create($data);

        return redirect()->route('admin.marketing.opportunities.index')
            ->with('status', __('Opportunity created.'));
    }

    public function edit(Opportunity $opportunity): View
    {
        return view('marketing::opportunities.edit', [
            'opportunity' => $opportunity,
            'customers' => Customer::orderBy('name')->get(),
        ]);
    }

    public function update(StoreOpportunityRequest $request, Opportunity $opportunity): RedirectResponse
    {
        $opportunity->update($request->validated());

        return redirect()->route('admin.marketing.opportunities.index')
            ->with('status', __('Opportunity updated.'));
    }

    public function destroy(Opportunity $opportunity): RedirectResponse
    {
        $opportunity->delete();

        return back()->with('status', __('Opportunity removed.'));
    }
}
