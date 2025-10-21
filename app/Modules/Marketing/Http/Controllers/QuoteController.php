<?php

namespace App\Modules\Marketing\Http\Controllers;

use App\Modules\Marketing\Domain\Models\Customer;
use App\Modules\Marketing\Domain\Models\CustomerContact;
use App\Modules\Marketing\Domain\Models\Quote;
use App\Modules\Marketing\Application\Services\PricingService;
use App\Modules\Marketing\Http\Requests\StoreQuoteRequest;
use App\Modules\Marketing\Http\Requests\UpdateQuoteRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QuoteController extends \App\Http\Controllers\Controller
{
    public function __construct()
    {
        $this->authorizeResource(Quote::class, 'quote');
    }

    public function index(Request $request): View
    {
        $query = Quote::query()->with('customer');

        $search = trim((string) $request->query('q', ''));
        $status = (string) $request->query('status', '');
        $customerId = (int) $request->query('customer_id', 0);

        if ($search !== '') {
            $query->where(function ($q) use ($search): void {
                $like = '%' . $search . '%';
                $q->where('quote_no', 'like', $like)
                    ->orWhereHas('customer', static function ($customerQuery) use ($like): void {
                        $customerQuery->where('name', 'like', $like)
                            ->orWhere('code', 'like', $like);
                    });
            });
        }

        if ($status !== '' && in_array($status, ['draft', 'sent', 'accepted', 'rejected', 'cancelled'], true)) {
            $query->where('status', $status);
        }

        if ($customerId > 0) {
            $query->where('customer_id', $customerId);
        }

        /** @var LengthAwarePaginator $quotes */
        $quotes = $query->latest()->paginate(15)->withQueryString();

        return view('marketing::quotes.index', [
            'quotes' => $quotes,
            'filters' => [
                'q' => $search,
                'status' => $status,
                'customer_id' => $customerId,
            ],
        ]);
    }

    public function create(): View
    {
        return view('marketing::quotes.create', [
            'customers' => Customer::orderBy('name')->get(),
            'contacts' => CustomerContact::orderBy('name')->get(),
        ]);
    }

    public function store(StoreQuoteRequest $request, PricingService $pricing): RedirectResponse
    {
        $data = $request->validated();
        $data['company_id'] = currentCompanyId();
        $data['quote_no'] = $data['quote_no'] ?: Quote::generateNumber($data['company_id']);

        $result = $pricing->calculate($data['lines']);

        $quote = Quote::create([
            'company_id' => $data['company_id'],
            'customer_id' => $data['customer_id'],
            'contact_id' => $data['contact_id'] ?? null,
            'quote_no' => $data['quote_no'],
            'date' => $data['date'],
            'currency' => $data['currency'],
            'status' => $data['status'],
            'subtotal' => $result['subtotal'],
            'discount_total' => $result['discount_total'],
            'tax_total' => $result['tax_total'],
            'grand_total' => $result['grand_total'],
            'notes' => $data['notes'] ?? null,
        ]);

        $this->syncLines($quote, $result['lines']);

        return redirect()->route('admin.marketing.quotes.show', $quote)
            ->with('status', __('Quote created successfully.'));
    }

    public function show(Quote $quote): View
    {
        $quote->load(['customer', 'contact', 'lines' => fn ($q) => $q->orderBy('sort_order')]);

        return view('marketing::quotes.show', [
            'quote' => $quote,
        ]);
    }

    public function print(Quote $quote): View
    {
        $this->authorize('view', $quote);

        $quote->load(['customer', 'lines' => fn ($q) => $q->orderBy('sort_order')]);

        return view('marketing::quotes.print', [
            'quote' => $quote,
        ]);
    }

    public function edit(Quote $quote): View
    {
        $quote->load(['lines' => fn ($q) => $q->orderBy('sort_order')]);

        return view('marketing::quotes.edit', [
            'quote' => $quote,
            'customers' => Customer::orderBy('name')->get(),
            'contacts' => CustomerContact::orderBy('name')->get(),
        ]);
    }

    public function update(UpdateQuoteRequest $request, Quote $quote, PricingService $pricing): RedirectResponse
    {
        $data = $request->validated();

        $result = $pricing->calculate($data['lines']);

        $quote->update([
            'customer_id' => $data['customer_id'],
            'contact_id' => $data['contact_id'] ?? null,
            'quote_no' => $data['quote_no'],
            'date' => $data['date'],
            'currency' => $data['currency'],
            'status' => $data['status'],
            'subtotal' => $result['subtotal'],
            'discount_total' => $result['discount_total'],
            'tax_total' => $result['tax_total'],
            'grand_total' => $result['grand_total'],
            'notes' => $data['notes'] ?? null,
        ]);

        $this->syncLines($quote, $result['lines']);

        return redirect()->route('admin.marketing.quotes.show', $quote)
            ->with('status', __('Quote updated successfully.'));
    }

    public function destroy(Quote $quote): RedirectResponse
    {
        $quote->delete();

        return redirect()->route('admin.marketing.quotes.index')
            ->with('status', __('Quote removed.'));
    }

    protected function syncLines(Quote $quote, array $lines): void
    {
        $quote->lines()->delete();

        foreach ($lines as $line) {
            $quote->lines()->create(array_merge($line, [
                'company_id' => $quote->company_id,
            ]));
        }
    }
}
