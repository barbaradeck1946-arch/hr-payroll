<?php

namespace App\Modules\Teams\Repositories;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Team;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class TeamRepository
{
    /**
     * @param array<string, mixed> $filters
     */
    public function paginate(array $filters): LengthAwarePaginator
    {
        $q = trim((string) ($filters['q'] ?? ''));
        $departmentId = (int) ($filters['department_id'] ?? 0);
        $status = (string) ($filters['status'] ?? '');
        $perPage = max(10, min(100, (int) ($filters['per_page'] ?? 20)));

        return Team::query()
            ->with(['department:id,name', 'lead:id,employee_code,first_name,last_name'])
            ->withCount(['projects', 'members'])
            ->when($q !== '', function ($query) use ($q): void {
                $query->where(function ($inner) use ($q): void {
                    $inner->where('name', 'like', "%{$q}%")
                        ->orWhere('code', 'like', "%{$q}%")
                        ->orWhere('description', 'like', "%{$q}%");
                });
            })
            ->when($departmentId > 0, fn ($query) => $query->where('department_id', $departmentId))
            ->when($status !== '', fn ($query) => $query->where('is_active', $status === 'active'))
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $attributes): Team
    {
        return Team::query()->create($attributes);
    }

    public function update(Team $team, array $attributes): void
    {
        $team->update($attributes);
    }

    public function delete(Team $team): void
    {
        $team->delete();
    }

    /** @return Collection<int, Department> */
    public function listDepartments(): Collection
    {
        return Department::query()->orderBy('name')->get(['id', 'name']);
    }

    /** @return Collection<int, Employee> */
    public function listActiveEmployees(): Collection
    {
        return Employee::query()
            ->where('employment_status', 'active')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'employee_code', 'first_name', 'last_name']);
    }

    public function withMembers(Team $team): Team
    {
        return $team->load([
            'department:id,name',
            'lead:id,employee_code,first_name,last_name',
            'members:id,employee_code,first_name,last_name',
        ]);
    }
}
