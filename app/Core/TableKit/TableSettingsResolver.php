<?php

namespace App\Core\TableKit;

use App\Core\Settings\SettingsRepository;

/**
 * TableKit liste davranışını modül ayarlarından çözer.
 */
class TableSettingsResolver
{
    public function __construct(private readonly SettingsRepository $settings)
    {
    }

    /**
     * @return array{default_sort:string,page_size:int,max_page_size:int,row_density:string,visible_columns:array<int,string>,enable_heavy_filters:bool,export_formats:array<int,string>,export_max_rows:int}
     */
    public function resolve(int $companyId, string $tableKey): array
    {
        $defaults = config('tablekit.defaults', []);
        $module = $this->extractModule($tableKey);
        $baseKey = sprintf('modules.%s.table.', $module);
        $exportKey = sprintf('modules.%s.export.', $module);

        $defaultSort = (string) ($this->settings->get($companyId, $baseKey . 'default_sort', $defaults['default_sort'] ?? '-created_at'));
        $pageSize = (int) ($this->settings->get($companyId, $baseKey . 'page_size', $defaults['page_size'] ?? 25));
        $maxPageSize = (int) ($this->settings->get($companyId, $baseKey . 'max_page_size', $defaults['max_page_size'] ?? 200));
        $rowDensity = (string) ($this->settings->get($companyId, $baseKey . 'row_density', $defaults['row_density'] ?? 'normal'));
        $visibleColumns = $this->settings->getJson($companyId, $baseKey . 'visible_columns', $defaults['visible_columns'] ?? []);
        $heavyFilters = $this->settings->getBool($companyId, $baseKey . 'enable_heavy_filters', (bool) ($defaults['enable_heavy_filters'] ?? true));
        $exportFormats = $this->settings->getJson($companyId, $exportKey . 'enabled_formats', $defaults['export_formats'] ?? ['csv', 'xlsx']);
        $exportMaxRows = (int) ($this->settings->get($companyId, $exportKey . 'max_rows', $defaults['export_max_rows'] ?? 100000));

        return [
            'default_sort' => $defaultSort,
            'page_size' => max(1, $pageSize),
            'max_page_size' => max(1, $maxPageSize),
            'row_density' => $rowDensity ?: 'normal',
            'visible_columns' => array_filter($visibleColumns, 'is_string'),
            'enable_heavy_filters' => (bool) $heavyFilters,
            'export_formats' => array_filter($exportFormats, 'is_string'),
            'export_max_rows' => max(1, $exportMaxRows),
        };
    }

    private function extractModule(string $tableKey): string
    {
        return explode(':', $tableKey, 2)[0] ?? 'core';
    }
}
