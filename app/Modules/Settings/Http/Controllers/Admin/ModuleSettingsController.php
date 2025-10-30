<?php

namespace App\Modules\Settings\Http\Controllers\Admin;

use App\Core\Settings\SettingsRepository;
use App\Http\Controllers\Controller;
use App\Modules\Settings\Http\Requests\Admin\ModuleSettingsRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use RuntimeException;

/**
 * Modül ayarlarının yönetimini sağlar.
 */
class ModuleSettingsController extends Controller
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

        $values = $this->settings->many($companyId, [
            'modules.drive.enable_versioning',
            'modules.inventory.low_stock_threshold',
            'modules.finance.default_currency',
            'modules.cms.feature_flags',
        ], [
            'modules.drive.enable_versioning' => '0',
            'modules.inventory.low_stock_threshold' => 25,
            'modules.finance.default_currency' => 'TRY',
            'modules.cms.feature_flags' => json_encode(['contact_form' => true, 'hero_banner' => true]),
        ]);

        $featureFlags = $this->settings->getJson($companyId, 'modules.cms.feature_flags', [
            'contact_form' => true,
            'hero_banner' => true,
        ]);

        return view('settings::admin.modules', [
            'values' => [
                'drive_enable_versioning' => filter_var($values['modules.drive.enable_versioning'], FILTER_VALIDATE_BOOLEAN),
                'inventory_low_stock_threshold' => (int) $values['modules.inventory.low_stock_threshold'],
                'finance_default_currency' => (string) $values['modules.finance.default_currency'],
                'cms_feature_flags' => json_encode($featureFlags, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            ],
        ]);
    }

    public function update(ModuleSettingsRequest $request): JsonResponse
    {
        $companyId = currentCompanyId();
        if (! $companyId) {
            throw new RuntimeException('Şirket kimliği çözümlenemedi.');
        }

        $featureFlags = $request->input('cms_feature_flags');
        $decoded = $featureFlags ? json_decode($featureFlags, true) : [];
        if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
            return response()->json([
                'message' => 'JSON formatı hatalı.',
            ], 422);
        }

        $this->settings->setMany($companyId, [
            'modules.drive.enable_versioning' => ['value' => $request->boolean('drive_enable_versioning'), 'type' => 'bool'],
            'modules.inventory.low_stock_threshold' => ['value' => $request->integer('inventory_low_stock_threshold'), 'type' => 'int'],
            'modules.finance.default_currency' => ['value' => strtoupper($request->string('finance_default_currency')->toString()), 'type' => 'string'],
            'modules.cms.feature_flags' => ['value' => $decoded ?? [], 'type' => 'json'],
        ], $request->user()?->getKey());

        return response()->json(['message' => 'Modül ayarları güncellendi.']);
    }
}
