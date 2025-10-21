<?php

namespace App\Modules\Settings\Http\Controllers;

use App\Core\Tenancy\Middleware\IdentifyTenant;
use App\Core\Support\Models\Company;
use App\Core\Support\Models\CompanyDomain;
use App\Http\Controllers\Controller;
use App\Modules\Settings\Http\Requests\StoreCompanyDomainRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CompanyDomainController extends Controller
{
    public function store(StoreCompanyDomainRequest $request): RedirectResponse
    {
        $this->authorize('create', CompanyDomain::class);

        $company = $this->resolveCompany($request);

        $domainValue = strtolower(trim((string) $request->input('domain')));

        $domain = $company->domains()->create([
            'domain' => $domainValue,
            'is_primary' => false,
        ]);

        if ($request->boolean('is_primary')) {
            $this->setPrimaryDomain($company, $domain);
        } else {
            IdentifyTenant::flushCacheForDomains($domain->domain);
        }

        return redirect()
            ->route('admin.settings.company.edit')
            ->with('status', 'Alan adı eklendi.');
    }

    public function makePrimary(Request $request, CompanyDomain $domain): RedirectResponse
    {
        $this->authorize('update', $domain);

        $company = $this->resolveCompany($request);

        $this->assertSameCompany($company, $domain);

        $this->setPrimaryDomain($company, $domain);

        return redirect()
            ->route('admin.settings.company.edit')
            ->with('status', 'Birincil alan adı güncellendi.');
    }

    public function destroy(Request $request, CompanyDomain $domain): RedirectResponse
    {
        $this->authorize('delete', $domain);

        $company = $this->resolveCompany($request);

        $this->assertSameCompany($company, $domain);

        if ($company->domains()->count() <= 1) {
            throw ValidationException::withMessages([
                'domain' => 'En az bir alan adı olmalıdır.',
            ]);
        }

        $wasPrimary = $domain->is_primary;
        $removedDomain = $domain->domain;

        if ($wasPrimary) {
            $replacement = $company->domains()
                ->whereKeyNot($domain->getKey())
                ->orderByDesc('is_primary')
                ->orderBy('domain')
                ->first();

            if (! $replacement) {
                throw ValidationException::withMessages([
                    'domain' => 'Birincil alan adı başka bir alan tanımlanmadan kaldırılamaz.',
                ]);
            }

            $this->setPrimaryDomain($company, $replacement);
        }

        $domain->delete();

        IdentifyTenant::flushCacheForDomains($removedDomain);

        return redirect()
            ->route('admin.settings.company.edit')
            ->with('status', 'Alan adı silindi.');
    }

    protected function setPrimaryDomain(Company $company, CompanyDomain $domain): void
    {
        $previous = $company->domain;

        $company->domains()
            ->where('id', '!=', $domain->id)
            ->update(['is_primary' => false]);

        $domain->forceFill(['is_primary' => true]);
        $domain->save();

        $company->domain = $domain->domain;
        $company->save();

        if ($previous && $previous !== $domain->domain) {
            $company->domains()->firstOrCreate(
                ['domain' => $previous],
                ['is_primary' => false]
            );
        }

        IdentifyTenant::flushCacheForDomains($domain->domain, $previous);
    }

    protected function assertSameCompany(Company $company, CompanyDomain $domain): void
    {
        if ((int) $company->id !== (int) $domain->company_id) {
            throw ValidationException::withMessages([
                'domain' => 'Alan adı bu şirkete ait değil.',
            ]);
        }
    }

    protected function resolveCompany(Request $request): Company
    {
        $company = $request->attributes->get('company');

        if ($company instanceof Company) {
            return $company;
        }

        if (app()->bound('company')) {
            $resolved = app('company');
            if ($resolved instanceof Company) {
                return $resolved;
            }
        }

        throw ValidationException::withMessages([
            'company' => 'Şirket bağlamı bulunamadı.',
        ]);
    }
}
