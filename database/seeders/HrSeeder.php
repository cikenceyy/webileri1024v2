<?php

namespace Database\Seeders;

use App\Models\User;
use App\Modules\HR\Domain\Models\Department;
use App\Modules\HR\Domain\Models\Employee;
use App\Modules\HR\Domain\Models\EmploymentType;
use App\Modules\HR\Domain\Models\Title;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class HrSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            1 => [
                ['code' => 'PRD', 'name' => 'Üretim'],
                ['code' => 'FIN', 'name' => 'Finans'],
                ['code' => 'SL', 'name' => 'Satış'],
            ],
            2 => [
                ['code' => 'STO', 'name' => 'Mağaza Operasyon'],
                ['code' => 'FIN', 'name' => 'Finans'],
            ],
        ];

        $titles = [
            1 => [
                ['code' => 'GM', 'name' => 'Genel Müdür'],
                ['code' => 'ACC', 'name' => 'Muhasebe Uzmanı'],
                ['code' => 'OPS', 'name' => 'Operasyon Uzmanı'],
                ['code' => 'INT', 'name' => 'Stajyer'],
            ],
            2 => [
                ['code' => 'OWN', 'name' => 'İşletme Sahibi'],
                ['code' => 'ACC', 'name' => 'Muhasebe Uzmanı'],
            ],
        ];

        $employmentTypes = [
            1 => [
                ['code' => 'FT', 'name' => 'Tam Zamanlı'],
                ['code' => 'PT', 'name' => 'Yarı Zamanlı'],
                ['code' => 'CTR', 'name' => 'Sözleşmeli'],
            ],
            2 => [
                ['code' => 'FT', 'name' => 'Tam Zamanlı'],
                ['code' => 'INT', 'name' => 'Staj'],
            ],
        ];

        foreach ([1, 2] as $companyId) {
            $departmentMap = [];
            foreach ($departments[$companyId] as $departmentData) {
                $department = Department::query()->updateOrCreate(
                    [
                        'company_id' => $companyId,
                        'code' => $departmentData['code'],
                    ],
                    [
                        'name' => $departmentData['name'],
                        'is_active' => true,
                    ]
                );
                $departmentMap[$departmentData['code']] = $department->id;
            }

            $titleMap = [];
            foreach ($titles[$companyId] as $titleData) {
                $title = Title::query()->updateOrCreate(
                    [
                        'company_id' => $companyId,
                        'code' => $titleData['code'],
                    ],
                    [
                        'name' => $titleData['name'],
                        'is_active' => true,
                    ]
                );
                $titleMap[$titleData['code']] = $title->id;
            }

            $employmentMap = [];
            foreach ($employmentTypes[$companyId] as $typeData) {
                $type = EmploymentType::query()->updateOrCreate(
                    [
                        'company_id' => $companyId,
                        'code' => $typeData['code'],
                    ],
                    [
                        'name' => $typeData['name'],
                        'is_active' => true,
                    ]
                );
                $employmentMap[$typeData['code']] = $type->id;
            }

            $this->seedEmployees($companyId, $departmentMap, $titleMap, $employmentMap);
        }
    }

    protected function seedEmployees(int $companyId, array $departments, array $titles, array $types): void
    {
        $records = [
            1 => [
                [
                    'user_email' => 'owner@acme.localhost',
                    'code' => 'EMP-ACM-001',
                    'name' => 'Ayşe Yönetici',
                    'department' => 'PRD',
                    'title' => 'GM',
                    'type' => 'FT',
                ],
                [
                    'user_email' => 'accountant@acme.localhost',
                    'code' => 'EMP-ACM-002',
                    'name' => 'Mehmet Muhasebe',
                    'department' => 'FIN',
                    'title' => 'ACC',
                    'type' => 'FT',
                ],
                [
                    'user_email' => 'operator@acme.localhost',
                    'code' => 'EMP-ACM-003',
                    'name' => 'Selin Operasyon',
                    'department' => 'SL',
                    'title' => 'OPS',
                    'type' => 'FT',
                ],
                [
                    'user_email' => 'intern@acme.localhost',
                    'code' => 'EMP-ACM-004',
                    'name' => 'Emir Stajyer',
                    'department' => 'SL',
                    'title' => 'INT',
                    'type' => 'PT',
                ],
            ],
            2 => [
                [
                    'user_email' => 'owner@beta.localhost',
                    'code' => 'EMP-BET-001',
                    'name' => 'Betül Patron',
                    'department' => 'STO',
                    'title' => 'OWN',
                    'type' => 'FT',
                ],
                [
                    'user_email' => 'accountant@beta.localhost',
                    'code' => 'EMP-BET-002',
                    'name' => 'Kerem Muhasebe',
                    'department' => 'FIN',
                    'title' => 'ACC',
                    'type' => 'FT',
                ],
            ],
        ];

        foreach ($records[$companyId] as $index => $data) {
            $user = User::query()->where('email', $data['user_email'])->first();

            Employee::query()->updateOrCreate(
                [
                    'company_id' => $companyId,
                    'code' => $data['code'],
                ],
                [
                    'name' => $data['name'],
                    'email' => $data['user_email'],
                    'phone' => '+90 212 100 0' . ($index + 1),
                    'department_id' => $departments[$data['department']] ?? null,
                    'title_id' => $titles[$data['title']] ?? null,
                    'employment_type_id' => $types[$data['type']] ?? null,
                    'hire_date' => Carbon::now()->subYears(2)->addMonths($index),
                    'is_active' => true,
                    'user_id' => $user?->id,
                ]
            );
        }
    }
}
