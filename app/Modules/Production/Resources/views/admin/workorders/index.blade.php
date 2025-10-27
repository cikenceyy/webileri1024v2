@extends('layouts.admin')

@section('title', 'İş Emirleri')
@section('module', 'Production')

@section('content')



    @php
        $tableKitRows = $tableKitRows ?? [];
        $tableKitPaginator = $tableKitPaginator ?? null;

        if (! $tableKitConfig instanceof \App\Core\Support\TableKit\TableConfig) {
            $configColumns = is_array($tableKitConfig) ? ($tableKitConfig['columns'] ?? []) : [];
            $configOptions = is_array($tableKitConfig) ? ($tableKitConfig['options'] ?? []) : [];

            if (! array_key_exists('default_sort', $configOptions) && isset($tableKitConfig['order'][0])) {
                $sortKey = (string) $tableKitConfig['order'][0];
                $direction = strtolower((string) ($tableKitConfig['order'][1] ?? 'asc'));
                $configOptions['default_sort'] = $direction === 'desc' ? '-' . $sortKey : $sortKey;
            }

            if (! array_key_exists('data_count', $configOptions)) {
                $configOptions['data_count'] = $tableKitPaginator?->total()
                    ?? (is_countable($tableKitRows) ? count($tableKitRows) : 0);
            }

            $tableKitConfig = \App\Core\Support\TableKit\TableConfig::make($configColumns, $configOptions);
        }
    @endphp
    <div class="card">
        <x-table :config="$tableKitConfig" :rows="$tableKitRows" :paginator="$tableKitPaginator">
            <x-slot name="toolbar">
                <x-table:toolbar :config="$tableKitConfig" :search-placeholder="__('İş emri ara…')" />
            </x-slot>
        </x-table>
    </div>
@endsection
