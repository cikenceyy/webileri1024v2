<?php

namespace Tests\Feature\TableKit;

use App\Core\Support\TableKit\TableConfig;
use Illuminate\Support\Collection;
use Tests\TestCase;

class PagesRenderTest extends TestCase
{
    public function test_component_renders_table_markup(): void
    {
        $config = TableConfig::make([
            ['key' => 'name', 'label' => 'Ad', 'type' => 'text'],
            ['key' => 'status', 'label' => 'Durum', 'type' => 'badge'],
        ], [
            'data_count' => 2,
        ]);

        $rows = [
            ['id' => 1, 'cells' => ['name' => 'Test', 'status' => 'draft']],
            ['id' => 2, 'cells' => ['name' => 'Demo', 'status' => 'issued']],
        ];

        $html = view('components.tablekit.table', [
            'config' => $config,
            'rows' => new Collection($rows),
            'paginator' => null,
        ])->render();

        $this->assertStringContainsString('data-tablekit="true"', $html);
        $this->assertStringContainsString('role="table"', $html);
        $this->assertStringContainsString('data-tablekit-mode="client"', $html);
    }
}
