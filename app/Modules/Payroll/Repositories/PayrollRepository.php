<?php

namespace App\Modules\Payroll\Repositories;

use App\Models\Bonus;
use App\Models\Employee;
use App\Models\EmployeeDeduction;
use App\Models\EmployeeLoan;
use App\Models\EmployeeProvidentFund;
use App\Models\PayrollRun;
use App\Models\SalaryTemplate;
use App\Models\User;
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
    public function loans(array $filters, ?User $user = null): LengthAwarePaginator
    {
        $canViewAll = $user?->hasAnyPermission(['payroll.manage-loan', 'loan.view', 'employee_loan.view']) ?? false;
        $canSupervisorApprove = $user?->hasAnyPermission(['loan.approve-supervisor', 'employee_loan.approve-supervisor']) ?? false;
        $canFinalApprove = $user?->hasAnyPermission(['loan.approve', 'loan.approve-final', 'employee_loan.approve', 'employee_loan.approve-final']) ?? false;
        $canViewOwn = $user?->hasAnyPermission(['loan.apply', 'employee_loan.apply', 'employee_loan.view-own']) ?? false;
        $employeeId = $user?->employee?->id;

        return EmployeeLoan::query()
            ->with(['employee:id,employee_code,first_name,last_name'])
            ->withSum('installments as paid_total', 'paid_amount')
            ->when(! $canViewAll, function ($query) use ($canSupervisorApprove, $canFinalApprove, $canViewOwn, $employeeId): void {
                $query->where(function ($inner) use ($canSupervisorApprove, $canFinalApprove, $canViewOwn, $employeeId): void {
                    if ($canViewOwn && $employeeId) {
                        $inner->where('employee_id', $employeeId);
                    }

                    if ($canSupervisorApprove && $employeeId) {
                        $inner->orWhere(function ($teamQuery) use ($employeeId): void {
                            $teamQuery
                                ->where('status', 'pending_supervisor')
                                ->whereHas('employee', fn ($employeeQuery) => $employeeQuery
                                    ->where('reports_to_id', $employeeId)
                                    ->orWhereHas('department', fn ($departmentQuery) => $departmentQuery->where('head_employee_id', $employeeId)));
                        });
                    }

                    if ($canFinalApprove) {
                        $inner->orWhere('status', 'pending_final');
                    }
                });
            })
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
    public function deductions(array $filters, ?User $user = null): LengthAwarePaginator
    {
        $employeeIds = $this->financeEmployeeScope($user, [
            'payroll.manage-deduction',
            'deduction.create',
            'deduction.update',
            'deduction.delete',
            'employee_deduction.create',
            'employee_deduction.update',
            'employee_deduction.delete',
        ]);

        return EmployeeDeduction::query()
            ->with('employee:id,employee_code,first_name,last_name')
            ->when($employeeIds !== null, fn ($query) => $query->whereIn('employee_id', $employeeIds))
            ->when((int) ($filters['employee_id'] ?? 0) > 0, fn ($query) => $query->where('employee_id', (int) $filters['employee_id']))
            ->when((string) ($filters['status'] ?? '') === 'active', fn ($query) => $query->where('is_active', true))
            ->when((string) ($filters['status'] ?? '') === 'inactive', fn ($query) => $query->where('is_active', false))
            ->orderByDesc('effective_from')
            ->orderByDesc('id')
            ->paginate($this->perPage($filters))
            ->withQueryString();
    }

    public function providentFunds(array $filters, ?User $user = null): LengthAwarePaginator
    {
        $employeeIds = $this->financeEmployeeScope($user, [
            'payroll.manage-pf',
            'provident_fund.create',
            'provident_fund.update',
            'provident_fund.adjust',
            'provident_fund.post-transaction',
        ]);

        return EmployeeProvidentFund::query()
            ->with('employee:id,employee_code,first_name,last_name')
            ->when($employeeIds !== null, fn ($query) => $query->whereIn('employee_id', $employeeIds))
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

    public function employeesForSelect(?array $employeeIds = null): Collection
    {
        return Employee::query()
            ->select(['id', 'employee_code', 'first_name', 'last_name', 'salary_grade_id'])
            ->with('salaryGrade:id,grade_name,min_salary,max_salary')
            ->whereNotIn('employment_status', ['resigned', 'terminated'])
            ->when($employeeIds !== null, fn ($query) => $query->whereIn('id', $employeeIds))
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();
    }

    public function activeEmployeesForSelect(): Collection
    {
        return Employee::query()
            ->select(['id', 'employee_code', 'first_name', 'last_name', 'salary_grade_id'])
            ->with('salaryGrade:id,grade_name,min_salary,max_salary')
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

    /**
     * @param array<int, string> $globalPermissions
     * @return array<int, int>|null
     */
    public function financeEmployeeScope(?User $user, array $globalPermissions): ?array
    {
        if (! $user) {
            return [];
        }

        if ($user->hasAnyPermission($globalPermissions)) {
            return null;
        }

        $employeeId = (int) ($user->employee?->id ?? 0);

        return $employeeId > 0 ? [$employeeId] : [];
    }
}
