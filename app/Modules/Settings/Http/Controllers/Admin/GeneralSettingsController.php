<?php

namespace App\Modules\Settings\Http\Controllers\Admin;

use App\Core\Settings\Models\CompanySetting;
use App\Core\Settings\SettingsRepository;
use App\Core\Support\Models\Company;
use App\Http\Controllers\Controller;
use App\Modules\Settings\Http\Requests\Admin\GeneralSettingsRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use RuntimeException;

/**
 * Genel ayarlar formunu görüntüler ve günceller.
 */
class GeneralSettingsController extends Controller
{
    public function __construct(private readonly SettingsRepository $settings)
    {
    }

    public function show(): View
    {
        $companyId = currentCompanyId();
        if (! $companyId) {
            throw new RuntimeException('Şirket kimliği çözümlenemedi.');
        }

        $company = Company::query()->find($companyId);
        $values = $this->settings->many($companyId, [
            'company.name',
            'company.locale',
            'company.timezone',
            'company.logo_path',
        ]);

        $logoPath = (string) ($values['company.logo_path'] ?? '');
        $logoUrl = $logoPath !== '' ? Storage::disk('public')->url($logoPath) : null;

        $languages = [
            ['code' => 'tr', 'label' => 'Türkçe'],
            ['code' => 'en', 'label' => 'English'],
        ];

        $timezones = collect(\DateTimeZone::listIdentifiers())->map(fn ($zone) => [
            'value' => $zone,
            'label' => $zone,
        ])->all();

        $latest = CompanySetting::query()
            ->where('company_id', $companyId)
            ->latest('updated_at')
            ->first();

        $updatedBy = null;
        if ($latest && $latest->updated_by) {
            $user = User::query()->find($latest->updated_by);
            $updatedBy = $user?->name ?? ('Kullanıcı #' . $latest->updated_by);
        }

        return view('settings::admin.general', [
            'company' => $company,
            'values' => [
                'name' => $values['company.name'] ?? $company?->name,
                'locale' => $values['company.locale'] ?? app()->getLocale(),
                'timezone' => $values['company.timezone'] ?? config('app.timezone'),
                'logo_url' => $logoUrl,
            ],
            'options' => [
                'languages' => $languages,
                'timezones' => $timezones,
            ],
            'meta' => [
                'updated_at' => $latest?->updated_at,
                'updated_by' => $updatedBy,
            ],
        ]);
    }

    public function update(GeneralSettingsRequest $request): JsonResponse
    {
        $companyId = currentCompanyId();
        if (! $companyId) {
            throw new RuntimeException('Şirket kimliği çözümlenemedi.');
        }

        $logoPath = (string) $this->settings->get($companyId, 'company.logo_path', '');
        if ($request->hasFile('company_logo')) {
            $logoPath = $request->file('company_logo')->store("company-logos/{$companyId}", 'public');
        }

        $this->settings->setMany($companyId, [
            'company.name' => ['value' => $request->string('company_name')->toString(), 'type' => 'string'],
            'company.locale' => ['value' => $request->string('company_locale')->toString(), 'type' => 'string'],
            'company.timezone' => ['value' => $request->string('company_timezone')->toString(), 'type' => 'string'],
            'company.logo_path' => ['value' => $logoPath, 'type' => 'string'],
        ], $request->user()?->getKey());

        $logoUrl = $logoPath !== '' ? Storage::disk('public')->url($logoPath) : null;

        return response()->json([
            'message' => 'Genel ayarlar güncellendi.',
            'logo_url' => $logoUrl,
        ]);
    }
}
