<?php

namespace App\Modules\Marketing\Http\Controllers;

use App\Modules\Marketing\Domain\Models\Customer;
use App\Modules\Marketing\Http\Requests\ImportCustomersRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ImportController extends \App\Http\Controllers\Controller
{
    public function form(): View
    {
        $this->authorize('import', Customer::class);

        return view('marketing::import.customers');
    }

    public function importCustomers(ImportCustomersRequest $request): RedirectResponse
    {
        $companyId = currentCompanyId();
        $created = 0;
        $updated = 0;

        if (($handle = fopen($request->file('file')->getRealPath(), 'rb')) === false) {
            return back()->with('status', __('Unable to read uploaded file.'));
        }

        $header = null;
        while (($row = fgetcsv($handle, 0, ',')) !== false) {
            if ($header === null) {
                $header = array_map('strtolower', $row);
                continue;
            }

            if (count($row) !== count($header)) {
                continue;
            }

            $record = array_combine($header, $row);
            if (! $record) {
                continue;
            }

            $code = trim((string) ($record['code'] ?? ''));
            $name = trim((string) ($record['name'] ?? ''));

            if ($code === '' || $name === '') {
                continue;
            }

            $payload = [
                'company_id' => $companyId,
                'name' => $name,
                'email' => $record['email'] ?? null,
                'phone' => $record['phone'] ?? null,
                'status' => $record['status'] ?? 'active',
            ];

            $customer = Customer::withTrashed()->where('company_id', $companyId)->where('code', $code)->first();

            if ($customer) {
                $customer->restore();
                $customer->update($payload);
                $updated++;
            } else {
                $payload['code'] = $code;
                Customer::create($payload);
                $created++;
            }
        }

        fclose($handle);

        return back()->with('status', __('Imported :created created, :updated updated customers.', ['created' => $created, 'updated' => $updated]));
    }
}
