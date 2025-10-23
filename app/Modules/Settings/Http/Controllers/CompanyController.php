<?php

namespace App\Modules\Settings\Http\Controllers;

use App\Core\Support\Models\Company;
use App\Http\Controllers\Controller;
use App\Modules\Drive\Domain\Models\Media;
use App\Modules\Settings\Domain\Models\CompanySetting;
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

        $settings = CompanySetting::query()
            ->withoutGlobalScopes()
            ->with('logoMedia')
            ->where('company_id', $company->id)
            ->first();

        if (! $settings) {
            $settings = CompanySetting::withoutEvents(function () use ($company, $request) {
                return CompanySetting::query()
                    ->withoutGlobalScopes()
                    ->create([
                        'company_id' => $company->id,
                        'name' => $company->name,
                        'created_by' => optional($request->user())->getKey(),
                        'updated_by' => optional($request->user())->getKey(),
                    ]);
            });

            $settings->load('logoMedia');
        }

        return view('settings::company.edit', [
            'company' => $company,
            'settings' => $settings,
            'logoMedia' => $settings->logoMedia,
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

        $settings = CompanySetting::query()
            ->withoutGlobalScopes()
            ->firstOrCreate(
                ['company_id' => $company->id],
                ['name' => $company->name]
            );

        $settings->fill([
            'name' => $data['name'],
            'legal_title' => $data['legal_title'] ?? null,
            'tax_office' => $data['tax_office'] ?? null,
            'tax_number' => $data['tax_number'] ?? null,
            'website' => $data['website'] ?? null,
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'logo_media_id' => $data['logo_id'] ?? null,
            'updated_by' => optional($request->user())->getKey(),
        ]);

        if ($settings->wasRecentlyCreated) {
            $settings->created_by = optional($request->user())->getKey();
        }

        $settings->save();

        $companyUpdates = ['name' => $settings->name];

        if (array_key_exists('logo_id', $data)) {
            $companyUpdates['logo_id'] = $data['logo_id'];
        }

        $company->forceFill($companyUpdates)->save();

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
