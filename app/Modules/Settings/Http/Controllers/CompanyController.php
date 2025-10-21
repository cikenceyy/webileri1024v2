<?php

namespace App\Modules\Settings\Http\Controllers;

use App\Core\Support\Models\Company;
use App\Http\Controllers\Controller;
use App\Modules\Drive\Domain\Models\Media;
use App\Modules\Settings\Http\Requests\UpdateCompanyRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CompanyController extends Controller
{
    public function edit(Request $request): View
    {
        $company = $this->resolveCompany($request);

        $this->authorize('view', $company);

        $company->loadMissing(['domains' => fn ($query) => $query->orderByDesc('is_primary')->orderBy('domain'), 'logo']);

        if (! $company->domains->contains(fn ($domain) => $domain->domain === $company->domain)) {
            $company->domains()->firstOrCreate(
                ['domain' => $company->domain],
                ['is_primary' => true]
            );

            $company->load(['domains' => fn ($query) => $query->orderByDesc('is_primary')->orderBy('domain')]);
        }

        return view('settings::company.edit', [
            'company' => $company,
            'domains' => $company->domains,
            'logoMedia' => $company->logo,
        ]);
    }

    public function update(UpdateCompanyRequest $request): RedirectResponse
    {
        $company = $this->resolveCompany($request);

        $this->authorize('update', $company);

        $data = $request->validated();

        if (array_key_exists('logo_id', $data)) {
            $data['logo_id'] = $this->ensureLogoBelongsToCompany($data['logo_id'], $company->id);
        }

        $company->fill([
            'name' => $data['name'],
            'theme_color' => isset($data['theme_color']) && $data['theme_color'] !== ''
                ? trim((string) $data['theme_color'])
                : null,
            'logo_id' => $data['logo_id'] ?? null,
        ]);

        $company->save();

        return redirect()
            ->route('admin.settings.company.edit')
            ->with('status', 'Şirket bilgileri güncellendi.');
    }

    protected function ensureLogoBelongsToCompany(?int $logoId, int $companyId): ?int
    {
        if (! $logoId) {
            return null;
        }

        $media = Media::query()
            ->where('company_id', $companyId)
            ->find($logoId);

        if (! $media) {
            throw ValidationException::withMessages([
                'logo_id' => 'Seçilen logo bu şirkete ait değil.',
            ]);
        }

        return $media->id;
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
