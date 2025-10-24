<?php

namespace App\Modules\HR\Providers;

use App\Modules\HR\Domain\Models\Department;
use App\Modules\HR\Domain\Models\Employee;
use App\Modules\HR\Domain\Models\EmploymentType;
use App\Modules\HR\Domain\Models\Title;
use App\Modules\HR\Policies\DepartmentPolicy;
use App\Modules\HR\Policies\EmployeePolicy;
use App\Modules\HR\Policies\EmploymentTypePolicy;
use App\Modules\HR\Policies\TitlePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class HRServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $configPath = __DIR__ . '/../Config';

        if (is_dir($configPath)) {
            foreach (glob($configPath . '/*.php') ?: [] as $configFile) {
                $name = pathinfo($configFile, PATHINFO_FILENAME);
                $this->mergeConfigFrom($configFile, 'hr.' . $name);
            }
        }
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/admin.php');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'hr');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/migrations');

        $this->registerPolicies();
    }

    protected function registerPolicies(): void
    {
        Gate::policy(Department::class, DepartmentPolicy::class);
        Gate::policy(Title::class, TitlePolicy::class);
        Gate::policy(EmploymentType::class, EmploymentTypePolicy::class);
        Gate::policy(Employee::class, EmployeePolicy::class);
    }
}
