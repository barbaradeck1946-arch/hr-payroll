<?php

namespace App\Modules\Payroll\Repositories;

use App\Models\Bonus;
use App\Models\Employee;
use App\Models\EmployeeDeduction;
use App\Models\EmployeeLoan;
use App\Models\EmployeeProvidentFund;
use App\Models\PayrollRun;
use App\Models\SalaryTemplate;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class PayrollRepository
{
    /**
     * @param array<string, mixed> $filters
     */
    public function salaryTemplates(array $filters): LengthAwarePaginator
    {
        $q = trim((string) ($filters['q'] ?? ''));

        return SalaryTemplate::query()
            ->withCount('employees')
            ->when($q !== '', fn (Builder $query) => $query
                ->where('name', 'like', "%{$q}%")
                ->orWhere('code', 'like', "%{$q}%"))
            ->orderBy('name')
            ->paginate($this->perPage($filters))
            ->withQueryString();
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function bonuses(array $filters): LengthAwarePaginator
    {
        return Bonus::query()
            ->with(['employee:id,employee_code,first_name,last_name', 'creator:id,name'])
            ->when((int) ($filters['employee_id'] ?? 0) > 0, fn ($query) => $query->where('employee_id', (int) $filters['employee_id']))
            ->orderByDesc('bonus_date')
            ->orderByDesc('id')
            ->paginate($this->perPage($filters))
            ->withQueryString();
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function loans(array $filters): LengthAwarePaginator
    {
        return EmployeeLoan::query()
            ->with(['employee:id,employee_code,first_name,last_name'])
            ->withSum('installments as paid_total', 'paid_amount')
            ->when((string) ($filters['status'] ?? '') !== '', fn ($query) => $query->where('status', $filters['status']))
            ->when((int) ($filters['employee_id'] ?? 0) > 0, fn ($query) => $query->where('employee_id', (int) $filters['employee_id']))
            ->orderByDesc('issued_date')
            ->orderByDesc('id')
            ->paginate($this->perPage($filters))
            ->withQueryString();
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function deductions(array $filters): LengthAwarePaginator
    {
        return EmployeeDeduction::query()
            ->with('employee:id,employee_code,first_name,last_name')
            ->when((int) ($filters['employee_id'] ?? 0) > 0, fn ($query) => $query->where('employee_id', (int) $filters['employee_id']))
            ->when((string) ($filters['status'] ?? '') === 'active', fn ($query) => $query->where('is_active', true))
            ->when((string) ($filters['status'] ?? '') === 'inactive', fn ($query) => $query->where('is_active', false))
            ->orderByDesc('effective_from')
            ->orderByDesc('id')
            ->paginate($this->perPage($filters))
            ->withQueryString();
    }

    public function providentFunds(array $filters): LengthAwarePaginator
    {
        return EmployeeProvidentFund::query()
            ->with('employee:id,employee_code,first_name,last_name')
            ->when((int) ($filters['employee_id'] ?? 0) > 0, fn ($query) => $query->where('employee_id', (int) $filters['employee_id']))
            ->orderByDesc('id')
            ->paginate($this->perPage($filters))
            ->withQueryString();
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function runs(array $filters): LengthAwarePaginator
    {
        return PayrollRun::query()
            ->withCount('items')
            ->with('processor:id,name')
            ->when((string) ($filters['status'] ?? '') !== '', fn ($query) => $query->where('status', $filters['status']))
            ->orderByDesc('period_start')
            ->orderByDesc('id')
            ->paginate($this->perPage($filters))
            ->withQueryString();
    }

    public function employeesForSelect(): Collection
    {
        return Employee::query()
            ->select(['id', 'employee_code', 'first_name', 'last_name', 'salary_grade_id'])
            ->where('employment_status', 'active')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();
    }

    public function templatesForSelect(): Collection
    {
        return SalaryTemplate::query()
            ->select([
                'id',
                'name',
                'code',
                'pay_frequency',
                'basic_salary',
                'house_rent',
                'medical_allowance',
                'conveyance_allowance',
                'other_allowance',
                'provident_fund_percent',
                'tax_percent',
            ])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    /**
     * @param array<string, mixed> $filters
     */
    private function perPage(array $filters): int
    {
        return max(10, min(100, (int) ($filters['per_page'] ?? 20)));
    }
}
