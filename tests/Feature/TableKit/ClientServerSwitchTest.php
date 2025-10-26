<?php

namespace Tests\Feature\TableKit;

use App\Core\Support\TableKit\TableConfig;
use Tests\TestCase;

class ClientServerSwitchTest extends TestCase
{
    public function test_mode_switches_based_on_threshold(): void
    {
        $config = TableConfig::make([
            ['key' => 'name', 'label' => 'Ad', 'type' => 'text'],
        ], [
            'client_threshold' => 500,
            'data_count' => 200,
        ]);

        $this->assertSame('client', $config->determineMode());

        $serverConfig = TableConfig::make([
            ['key' => 'name', 'label' => 'Ad', 'type' => 'text'],
        ], [
            'client_threshold' => 100,
            'data_count' => 350,
        ]);

        $this->assertSame('server', $serverConfig->determineMode());
    }
}
