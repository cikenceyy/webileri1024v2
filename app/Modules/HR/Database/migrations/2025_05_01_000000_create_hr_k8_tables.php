<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('departments')) {
            Schema::create('departments', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
                $table->string('code', 32);
                $table->string('name');
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->unique(['company_id', 'code']);
            });
        }

        if (! Schema::hasTable('titles')) {
            Schema::create('titles', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
                $table->string('code', 32);
                $table->string('name');
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->unique(['company_id', 'code']);
            });
        }

        if (! Schema::hasTable('employment_types')) {
            Schema::create('employment_types', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
                $table->string('code', 32);
                $table->string('name');
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->unique(['company_id', 'code']);
            });
        }

        if (! Schema::hasTable('employees')) {
            Schema::create('employees', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
                $table->string('code', 32);
                $table->string('name');
                $table->string('email')->nullable();
                $table->string('phone')->nullable();
                $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
                $table->foreignId('title_id')->nullable()->constrained('titles')->nullOnDelete();
                $table->foreignId('employment_type_id')->nullable()->constrained('employment_types')->nullOnDelete();
                $table->date('hire_date')->nullable();
                $table->date('termination_date')->nullable();
                $table->boolean('is_active')->default(true);
                $table->text('notes')->nullable();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->timestamps();

                $table->unique(['company_id', 'code']);
                $table->index(['company_id', 'is_active']);
            });
        } else {
            Schema::table('employees', function (Blueprint $table): void {
                if (! Schema::hasColumn('employees', 'code')) {
                    $table->string('code', 32)->after('company_id');
                }
                if (! Schema::hasColumn('employees', 'department_id')) {
                    $table->foreignId('department_id')->nullable()->after('phone')->constrained('departments')->nullOnDelete();
                }
                if (! Schema::hasColumn('employees', 'title_id')) {
                    $table->foreignId('title_id')->nullable()->after('department_id')->constrained('titles')->nullOnDelete();
                }
                if (! Schema::hasColumn('employees', 'employment_type_id')) {
                    $table->foreignId('employment_type_id')->nullable()->after('title_id')->constrained('employment_types')->nullOnDelete();
                }
                if (! Schema::hasColumn('employees', 'hire_date')) {
                    $table->date('hire_date')->nullable()->after('employment_type_id');
                }
                if (! Schema::hasColumn('employees', 'termination_date')) {
                    $table->date('termination_date')->nullable()->after('hire_date');
                }
                if (! Schema::hasColumn('employees', 'is_active')) {
                    $table->boolean('is_active')->default(true)->after('termination_date');
                }
                if (! Schema::hasColumn('employees', 'notes')) {
                    $table->text('notes')->nullable()->after('is_active');
                }
                if (! Schema::hasColumn('employees', 'user_id')) {
                    $table->foreignId('user_id')->nullable()->after('notes')->constrained()->nullOnDelete();
                }

                $table->unique(['company_id', 'code']);
                $table->index(['company_id', 'is_active']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
        Schema::dropIfExists('employment_types');
        Schema::dropIfExists('titles');
        Schema::dropIfExists('departments');
    }
};
