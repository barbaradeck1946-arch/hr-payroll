<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add employee-specific salary snapshot values to salary assignments.
     */
    public function up(): void
    {
        Schema::table('employee_salary_templates', function (Blueprint $table): void {
            $table->decimal('basic_salary', 14, 2)->default(0)->after('pay_frequency');
            $table->decimal('house_rent', 14, 2)->default(0)->after('basic_salary');
            $table->decimal('medical_allowance', 14, 2)->default(0)->after('house_rent');
            $table->decimal('conveyance_allowance', 14, 2)->default(0)->after('medical_allowance');
            $table->decimal('other_allowance', 14, 2)->default(0)->after('conveyance_allowance');
            $table->decimal('gross_salary', 14, 2)->default(0)->after('other_allowance');
            $table->decimal('provident_fund_percent', 5, 2)->default(0)->after('gross_salary');
            $table->decimal('tax_percent', 5, 2)->default(0)->after('provident_fund_percent');
            $table->decimal('ctc_amount', 14, 2)->nullable()->after('tax_percent');
            $table->text('notes')->nullable()->after('ctc_amount');
        });

        DB::table('employee_salary_templates')
            ->join('salary_templates', 'salary_templates.id', '=', 'employee_salary_templates.salary_template_id')
            ->update([
                'employee_salary_templates.basic_salary' => DB::raw('salary_templates.basic_salary'),
                'employee_salary_templates.house_rent' => DB::raw('salary_templates.house_rent'),
                'employee_salary_templates.medical_allowance' => DB::raw('salary_templates.medical_allowance'),
                'employee_salary_templates.conveyance_allowance' => DB::raw('salary_templates.conveyance_allowance'),
                'employee_salary_templates.other_allowance' => DB::raw('salary_templates.other_allowance'),
                'employee_salary_templates.gross_salary' => DB::raw('salary_templates.basic_salary + salary_templates.house_rent + salary_templates.medical_allowance + salary_templates.conveyance_allowance + salary_templates.other_allowance'),
                'employee_salary_templates.provident_fund_percent' => DB::raw('salary_templates.provident_fund_percent'),
                'employee_salary_templates.tax_percent' => DB::raw('salary_templates.tax_percent'),
            ]);
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        Schema::table('employee_salary_templates', function (Blueprint $table): void {
            $table->dropColumn([
                'basic_salary',
                'house_rent',
                'medical_allowance',
                'conveyance_allowance',
                'other_allowance',
                'gross_salary',
                'provident_fund_percent',
                'tax_percent',
                'ctc_amount',
                'notes',
            ]);
        });
    }
};
