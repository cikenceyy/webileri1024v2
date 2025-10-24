<?php

namespace App\Modules\Settings\Http\Controllers\Admin;

use App\Core\Support\Models\Company;
use App\Http\Controllers\Controller;
use App\Modules\Settings\Domain\Models\Setting;
use App\Modules\Settings\Domain\SettingsDTO;
use App\Modules\Settings\Domain\SettingsService;
use App\Modules\Settings\Http\Requests\Admin\UpdateSettingsRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SettingsController extends Controller
{
    public function __construct(protected SettingsService $service)
    {
    }

    public function index(Request $request): View
    {
        $company = $this->resolveCompany($request);

        $this->authorize('viewAny', Setting::class);

        $settings = $this->service->get($company->id);
        $record = Setting::query()
            ->with('updatedByUser')
            ->where('company_id', $company->id)
            ->first();

        return view('settings::admin.index', [
            'company' => $company,
            'settings' => $settings,
            'meta' => [
                'version' => $record?->version ?? 1,
                'updated_at' => $record?->updated_at,
                'updated_by' => $record?->updatedByUser?->name,
            ],
        ]);
    }

    public function store(UpdateSettingsRequest $request): RedirectResponse
    {
        $company = $this->resolveCompany($request);

        $this->authorize('update', Setting::class);

        $payload = $request->validated();
        $dto = SettingsDTO::fromArray($payload);

        $this->service->update($company->id, $dto, (int) $request->user()->getKey());

        return redirect()
            ->route('admin.settings.index')
            ->with('status', __('Ayarlar başarıyla güncellendi.'));
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
            'company' => __('Şirket bağlamı bulunamadı.'),
        ]);
    }
}
