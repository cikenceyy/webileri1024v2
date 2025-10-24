<?php

namespace App\Modules\Settings\Domain;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

class SettingsDTO implements Arrayable
{
    /**
     * @param array{base_currency:string,allowed_currencies:array<int,string>} $money
     * @param array{default_vat_rate:float|int,withholding_enabled:bool} $tax
     * @param array{
     *     invoice_prefix:string,
     *     receipt_prefix:string,
     *     order_prefix:string,
     *     shipment_prefix:string,
     *     work_order_prefix:string,
     *     padding:int,
     *     reset_policy:string
     * } $sequencing
     * @param array{
     *     payment_terms_days:int,
     *     warehouse_id:int|null,
     *     price_list_id:int|null,
     *     tax_inclusive:bool,
     *     production_issue_warehouse_id:int|null,
     *     production_receipt_warehouse_id:int|null
     * } $defaults
     * @param array{
     *     invoice_print_template:string|null,
     *     shipment_note_template:string|null
     * } $documents
     * @param array{
     *     company_locale:string,
     *     timezone:string,
     *     decimal_precision:int
     * } $general
     */
    public function __construct(
        public array $money,
        public array $tax,
        public array $sequencing,
        public array $defaults,
        public array $documents,
        public array $general,
    ) {
    }

    public static function defaults(): self
    {
        return new self(
            money: [
                'base_currency' => 'USD',
                'allowed_currencies' => ['USD'],
            ],
            tax: [
                'default_vat_rate' => 18.0,
                'withholding_enabled' => false,
            ],
            sequencing: [
                'invoice_prefix' => 'INV',
                'receipt_prefix' => 'RCPT',
                'order_prefix' => 'ORD',
                'shipment_prefix' => 'SHP',
                'work_order_prefix' => 'WO',
                'padding' => 6,
                'reset_policy' => 'yearly',
            ],
            defaults: [
                'payment_terms_days' => 30,
                'warehouse_id' => null,
                'price_list_id' => null,
                'tax_inclusive' => false,
                'production_issue_warehouse_id' => null,
                'production_receipt_warehouse_id' => null,
            ],
            documents: [
                'invoice_print_template' => null,
                'shipment_note_template' => null,
            ],
            general: [
                'company_locale' => 'tr_TR',
                'timezone' => 'Europe/Istanbul',
                'decimal_precision' => 2,
            ],
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromArray(array $payload): self
    {
        $base = self::defaults()->toArray();
        $merged = array_replace_recursive($base, $payload);

        return new self(
            money: self::normalizeMoney($merged['money'] ?? []),
            tax: self::normalizeTax($merged['tax'] ?? []),
            sequencing: self::normalizeSequencing($merged['sequencing'] ?? []),
            defaults: self::normalizeDefaults($merged['defaults'] ?? []),
            documents: self::normalizeDocuments($merged['documents'] ?? []),
            general: self::normalizeGeneral($merged['general'] ?? []),
        );
    }

    public function toArray(): array
    {
        return [
            'money' => $this->money,
            'tax' => $this->tax,
            'sequencing' => $this->sequencing,
            'defaults' => $this->defaults,
            'documents' => $this->documents,
            'general' => $this->general,
        ];
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return Arr::get($this->toArray(), $key, $default);
    }

    public function defaultsSection(): array
    {
        return $this->defaults;
    }

    /**
     * @param array<string, mixed> $data
     * @return array{base_currency:string,allowed_currencies:array<int,string>}
     */
    protected static function normalizeMoney(array $data): array
    {
        $base = strtoupper((string) ($data['base_currency'] ?? 'USD'));
        $allowed = $data['allowed_currencies'] ?? [$base];
        $allowed = array_values(array_unique(array_map(static fn ($code) => strtoupper((string) $code), Arr::wrap($allowed))));

        if (! in_array($base, $allowed, true)) {
            array_unshift($allowed, $base);
            $allowed = array_values(array_unique($allowed));
        }

        return [
            'base_currency' => $base,
            'allowed_currencies' => $allowed,
        ];
    }

    /**
     * @param array<string, mixed> $data
     * @return array{default_vat_rate:float,withholding_enabled:bool}
     */
    protected static function normalizeTax(array $data): array
    {
        return [
            'default_vat_rate' => (float) ($data['default_vat_rate'] ?? 18.0),
            'withholding_enabled' => (bool) ($data['withholding_enabled'] ?? false),
        ];
    }

    /**
     * @param array<string, mixed> $data
     * @return array{
     *     invoice_prefix:string,
     *     receipt_prefix:string,
     *     order_prefix:string,
     *     shipment_prefix:string,
     *     padding:int,
     *     reset_policy:string
     * }
     */
    protected static function normalizeSequencing(array $data): array
    {
        return [
            'invoice_prefix' => (string) ($data['invoice_prefix'] ?? 'INV'),
            'receipt_prefix' => (string) ($data['receipt_prefix'] ?? 'RCPT'),
            'order_prefix' => (string) ($data['order_prefix'] ?? 'ORD'),
            'shipment_prefix' => (string) ($data['shipment_prefix'] ?? 'SHP'),
            'work_order_prefix' => (string) ($data['work_order_prefix'] ?? 'WO'),
            'padding' => (int) ($data['padding'] ?? 6),
            'reset_policy' => (string) ($data['reset_policy'] ?? 'yearly'),
        ];
    }

    /**
     * @param array<string, mixed> $data
     * @return array{
     *     payment_terms_days:int,
     *     warehouse_id:int|null,
     *     price_list_id:int|null,
     *     tax_inclusive:bool
     * }
     */
    protected static function normalizeDefaults(array $data): array
    {
        return [
            'payment_terms_days' => (int) ($data['payment_terms_days'] ?? 30),
            'warehouse_id' => isset($data['warehouse_id']) ? (int) $data['warehouse_id'] : null,
            'price_list_id' => isset($data['price_list_id']) ? (int) $data['price_list_id'] : null,
            'tax_inclusive' => (bool) ($data['tax_inclusive'] ?? false),
            'production_issue_warehouse_id' => isset($data['production_issue_warehouse_id']) ? (int) $data['production_issue_warehouse_id'] : null,
            'production_receipt_warehouse_id' => isset($data['production_receipt_warehouse_id']) ? (int) $data['production_receipt_warehouse_id'] : null,
        ];
    }

    /**
     * @param array<string, mixed> $data
     * @return array{invoice_print_template:string|null,shipment_note_template:string|null}
     */
    protected static function normalizeDocuments(array $data): array
    {
        return [
            'invoice_print_template' => isset($data['invoice_print_template']) ? (string) $data['invoice_print_template'] : null,
            'shipment_note_template' => isset($data['shipment_note_template']) ? (string) $data['shipment_note_template'] : null,
        ];
    }

    /**
     * @param array<string, mixed> $data
     * @return array{company_locale:string,timezone:string,decimal_precision:int}
     */
    protected static function normalizeGeneral(array $data): array
    {
        return [
            'company_locale' => (string) ($data['company_locale'] ?? 'tr_TR'),
            'timezone' => (string) ($data['timezone'] ?? 'Europe/Istanbul'),
            'decimal_precision' => (int) ($data['decimal_precision'] ?? 2),
        ];
    }
}
