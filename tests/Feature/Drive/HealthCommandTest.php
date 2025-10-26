<?php

namespace Tests\Feature\Drive;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HealthCommandTest extends TestCase
{
    public function test_drive_health_command_reports_success_with_fake_disk(): void
    {
        Storage::fake('local');
        Config::set('drive.disk', 'local');
        Config::set('filesystems.default', 'local');

        $this->artisan('drive:health')->assertExitCode(0);
    }
}
