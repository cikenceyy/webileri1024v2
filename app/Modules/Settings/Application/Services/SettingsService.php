<?php

namespace App\Modules\Settings\Application\Services;

use App\Core\Support\Models\Company as TenantCompany;
use App\Modules\Settings\Application\DTO\AddressDTO;
use App\Modules\Settings\Application\DTO\CompanyDTO;
use App\Modules\Settings\Application\DTO\CurrencyDTO;
use App\Modules\Settings\Application\DTO\DefaultsDTO;
use App\Modules\Settings\Application\DTO\DocumentTemplateDTO;
use App\Modules\Settings\Application\DTO\MetaDTO;
use App\Modules\Settings\Application\DTO\SequenceDTO;
use App\Modules\Settings\Application\DTO\SettingsDTO;
use App\Modules\Settings\Application\DTO\TaxDTO;
use App\Modules\Settings\Application\DTO\TaxRateDTO;
use App\Modules\Settings\Domain\Models\CompanySetting;
use App\Modules\Settings\Domain\Models\DocumentSequence;
use App\Modules\Settings\Domain\Models\DocumentTemplate;
use Illuminate\Contracts\Cache\Repository;

class SettingsService implements SettingsServiceInterface
{
    public function __construct(
        private readonly Repository $cache
    ) {
    }

    public function companySettings(int $companyId): SettingsDTO
    {
        $company = CompanySetting::query()
            ->withoutGlobalScopes()
            ->with('logoMedia')
            ->where('company_id', $companyId)
            ->first();

        if (! $company) {
            $tenant = TenantCompany::query()->findOrFail($companyId);

            $company = CompanySetting::withoutEvents(function () use ($companyId, $tenant) {
                return CompanySetting::query()
                    ->withoutGlobalScopes()
                    ->create([
                        'company_id' => $companyId,
                        'name' => $tenant->name,
                        'created_by' => null,
                        'updated_by' => null,
                    ]);
            });

            $company->load('logoMedia');
        }

        $version = max(1, (int) $company->version);
        $cacheKey = sprintf('company_settings:%d:%d', $companyId, $version);

        return $this->cache->remember($cacheKey, now()->addHour(), function () use ($company, $companyId, $version) {
            $addressesCollection = $company->addresses()
                ->orderByDesc('is_default')
                ->orderBy('type')
                ->get()
                ->map(fn ($address) => new AddressDTO(
                    type: $address->type,
                    country: $address->country,
                    city: $address->city,
                    district: $address->district,
                    addressLine: $address->address_line,
                    postalCode: $address->postal_code,
                    isDefault: (bool) $address->is_default
                ));

            $addresses = array_merge(
                ['hq' => [], 'shipping' => [], 'billing' => []],
                $addressesCollection
                    ->groupBy(fn (AddressDTO $address) => $address->type)
                    ->map(fn ($items) => $items->values()->all())
                    ->all()
            );

            $taxProfile = $company->tax;
            $taxRates = $taxProfile?->rates()
                ->orderByDesc('is_active')
                ->orderBy('name')
                ->get()
                ->map(fn ($rate) => new TaxRateDTO(
                    id: (int) $rate->id,
                    name: $rate->name,
                    rate: (float) $rate->rate,
                    active: (bool) $rate->is_active
                ))
                ->all() ?? [];

            $tax = new TaxDTO($taxProfile?->default_vat_id, $taxRates);

            $currencyModel = $company->currency;
            $currency = new CurrencyDTO(
                baseCurrency: $currencyModel?->base_currency ?? 'TRY',
                precisionPrice: (int) ($currencyModel?->precision_price ?? 2),
                exchangePolicy: $currencyModel?->exchange_policy ?? 'manual'
            );

            $sequenceModels = DocumentSequence::query()
                ->withoutGlobalScopes()
                ->where('company_id', $companyId)
                ->orderBy('doc_type')
                ->get();

            $sequences = [];
            foreach ($sequenceModels as $sequenceModel) {
                $preview = $this->formatPreview(
                    (string) $sequenceModel->prefix,
                    (int) $sequenceModel->zero_pad,
                    (int) $sequenceModel->next_no
                );

                $sequences[$sequenceModel->doc_type] = new SequenceDTO(
                    docType: $sequenceModel->doc_type,
                    prefix: $sequenceModel->prefix,
                    zeroPad: (int) $sequenceModel->zero_pad,
                    nextNo: (int) $sequenceModel->next_no,
                    resetRule: $sequenceModel->reset_rule,
                    preview: $preview
                );
            }

            $defaultsModel = $company->defaults;
            $defaults = new DefaultsDTO(
                defaultWarehouseId: $defaultsModel?->default_warehouse_id,
                defaultTaxId: $defaultsModel?->default_tax_id,
                defaultPaymentTerms: $defaultsModel?->default_payment_terms,
                defaultPrintTemplate: $defaultsModel?->default_print_template,
                defaultCountry: $defaultsModel?->default_country,
                defaultCity: $defaultsModel?->default_city,
                logisticsDefaults: $defaultsModel?->logistics_defaults ?? [],
                financeDefaults: $defaultsModel?->finance_defaults ?? []
            );

            $documents = DocumentTemplate::query()
                ->withoutGlobalScopes()
                ->where('company_id', $companyId)
                ->orderBy('code')
                ->get()
                ->map(fn ($document) => new DocumentTemplateDTO(
                    id: (int) $document->id,
                    code: $document->code,
                    printHeaderHtml: $document->print_header_html,
                    printFooterHtml: $document->print_footer_html,
                    watermarkText: $document->watermark_text
                ))
                ->all();

            $meta = new MetaDTO(
                version: $version,
                updatedAt: optional($company->updated_at)->toISOString()
            );

            return new SettingsDTO(
                company: new CompanyDTO(
                    name: $company->name,
                    legalTitle: $company->legal_title,
                    taxOffice: $company->tax_office,
                    taxNumber: $company->tax_number,
                    website: $company->website,
                    email: $company->email,
                    phone: $company->phone,
                    logoMediaId: $company->logo_media_id
                ),
                addresses: $addresses,
                tax: $tax,
                currency: $currency,
                sequences: $sequences,
                defaults: $defaults,
                documents: $documents,
                meta: $meta
            );
        });
    }

    private function formatPreview(?string $prefix, int $zeroPad, int $nextNo): string
    {
        $serial = str_pad((string) max($nextNo, 1), max($zeroPad, 1), '0', STR_PAD_LEFT);

        return trim(($prefix ?? '') . ' ' . $serial);
    }
}
