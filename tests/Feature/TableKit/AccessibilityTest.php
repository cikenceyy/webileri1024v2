<?php

namespace Tests\Feature\TableKit;

use App\Core\Support\TableKit\TableConfig;
use Tests\TestCase;

class AccessibilityTest extends TestCase
{
    public function test_table_has_accessibility_attributes(): void
    {
        $config = TableConfig::make([
            ['key' => 'name', 'label' => 'Ad', 'type' => 'text', 'sortable' => true],
        ], [
            'data_count' => 1,
        ]);

        $html = view('components.tablekit.table', [
            'config' => $config,
            'rows' => [['id' => 1, 'cells' => ['name' => 'TableKit']]],
            'paginator' => null,
        ])->render();

        $this->assertStringContainsString('aria-live="polite"', $html);
        $this->assertStringContainsString('aria-sort="none"', $html);
        $this->assertStringContainsString('tabindex="0"', $html);
    }
}
