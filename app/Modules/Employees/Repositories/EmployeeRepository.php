<?php

namespace App\Modules\Employees\Repositories;

use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\SalaryGrade;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

class EmployeeRepository
{
    /**
     * @param array<string, mixed> $filters
     * @return LengthAwarePaginator<Employee>
     */
    public function paginate(array $filters): LengthAwarePaginator
    {
        $q = trim((string) ($filters['q'] ?? ''));
        $departmentId = (int) ($filters['department_id'] ?? 0);
        $designationId = (int) ($filters['designation_id'] ?? 0);
        $employmentStatus = (string) ($filters['employment_status'] ?? '');
        $employmentType = (string) ($filters['employment_type'] ?? '');
        $perPage = max(10, min(100, (int) ($filters['per_page'] ?? 20)));

        return Employee::query()
            ->with([
                'department:id,name,code',
                'designation:id,name,code',
                'salaryGrade:id,grade_name,grade_code',
                'manager:id,employee_code,first_name,last_name',
                'user:id,name,email',
            ])
            ->when($q !== '', function ($query) use ($q): void {
                $query->where(function ($inner) use ($q): void {
                    $inner
                        ->where('employee_code', 'like', "%{$q}%")
                            ->orWhere('first_name', 'like', "%{$q}%")
                        ->orWhere('last_name', 'like', "%{$q}%")
                            ->orWhere('phone', 'like', "%{$q}%")
                        ->orWhere('work_email', 'like', "%{$q}%");
                });
            })
            ->when($departmentId > 0, fn ($query) => $query->where('department_id', $departmentId))
            ->when($designationId > 0, fn ($query) => $query->where('designation_id', $designationId))
            ->when($employmentStatus !== '', fn ($query) => $query->where('employment_status', $employmentStatus))
            ->when($employmentType !== '', fn ($query) => $query->where('employment_type', $employmentType))
            ->orderByDesc('date_of_joining')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    // create, update, delete, withDetails
    public function create(array $attributes): Employee
    {
        return Employee::query()->create($attributes);
    }

    public function update(Employee $employee, array $attributes): void
    {
        $employee->update($attributes);
    }

    public function delete(Employee $employee): void
    {
        $employee->delete();
    }

    public function withDetails(Employee $employee): Employee
    {
        $employee->load([
            'department:id,name,code',
            'designation:id,name,code',
            'salaryGrade:id,grade_name,grade_code,band_name,min_salary,max_salary',
            'manager:id,employee_code,first_name,last_name',
            'subordinates:id,employee_code,first_name,last_name,reports_to_id',
            'user:id,name,email',
        ]);

        return $employee;
    }


    public function listDepartments(): Collection
    {
        return Department::query()->orderBy('name')->get(['id', 'name', 'code']);
    }

    /**
     * @return Collection<int, Designation>
     */
    public function listDesignations(): Collection
    {
        return Designation::query()->orderBy('name')->get(['id', 'name', 'code', 'department_id']);
    }

    /**
     * @return Collection<int, SalaryGrade>
     */
    public function listSalaryGrades(): Collection
    {
        return SalaryGrade::query()->where('is_active', true)->orderBy('grade_name')->get(['id', 'grade_name', 'grade_code', 'band_name']);
    }

    /**
     * @return Collection<int, Employee>
     */
    public function listManagers(?int $excludeEmployeeId = null): Collection
    {
        return Employee::query()
            ->select(['id', 'employee_code', 'first_name', 'last_name'])
            ->when($excludeEmployeeId, fn ($query) => $query->where('id', '!=', $excludeEmployeeId))
            ->where('employment_status', 'active')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();
    }

    /**
     * @return Collection<int, User>
     */
    public function listUsersForLinking(?int $currentUserId = null): Collection
    {
        return User::query()
            ->select(['id', 'name', 'email'])
            ->where(function ($query) use ($currentUserId): void {
                $query->whereDoesntHave('employee');

                if ($currentUserId !== null) {
                    $query->orWhere('id', $currentUserId);
                }
            })
            ->orderBy('name')
            ->get();
    }

    public function nextSequenceNumber(): int
    {
        return ((int) Employee::withTrashed()->max('id')) + 1;
    }

    public function existsByCode(string $employeeCode, ?int $ignoreEmployeeId = null): bool
    {
        return Employee::query()
            ->when($ignoreEmployeeId, fn ($query) => $query->where('id', '!=', $ignoreEmployeeId))
            ->where('employee_code', $employeeCode)
            ->exists();
    }

    /**
     * @return Collection<int, Employee>
     */
    public function listForOrganizationStructure(): Collection
    {
        return Employee::query()
            ->with([
                'department:id,name,code',
                'designation:id,name,code',
                'manager:id,employee_code,first_name,last_name',
            ])
            ->orderBy('department_id')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get([
                'id',
                'employee_code',
                'first_name',
                'last_name',
                'department_id',
                'designation_id',
                'reports_to_id',
                'employment_status',
            ]);
    }

    /**
     * @return SupportCollection<int, Employee>
     */
    public function supervisorChain(Employee $employee): SupportCollection
    {
        $chain = collect();
        $visited = [];
        $current = $employee;

        while ($current->reports_to_id) {
            if (isset($visited[$current->reports_to_id])) {
                break;
            }

            $manager = Employee::query()
                ->with(['department:id,name', 'designation:id,name'])
                ->find($current->reports_to_id, [
                    'id',
                    'employee_code',
                    'first_name',
                    'last_name',
                    'department_id',
                    'designation_id',
                    'reports_to_id',
                ]);

            if (! $manager) {
                break;
            }

            $chain->push($manager);
            $visited[$manager->id] = true;
            $current = $manager;
        }

        return $chain;
    }
}
