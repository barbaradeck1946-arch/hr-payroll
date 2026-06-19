<?php

namespace App\Modules\Projects\Repositories;

use App\Models\Employee;
use App\Models\Project;
use App\Models\Team;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ProjectRepository
{
    /**
     * @param array<string, mixed> $filters
     */
    public function paginate(array $filters): LengthAwarePaginator
    {
        $q = trim((string) ($filters['q'] ?? ''));
        $teamId = (int) ($filters['team_id'] ?? 0);
        $status = (string) ($filters['status'] ?? '');
        $perPage = max(10, min(100, (int) ($filters['per_page'] ?? 20)));

        return Project::query()
            ->with(['team:id,name,code', 'manager:id,employee_code,first_name,last_name'])
            ->withCount(['tasks', 'members'])
            ->when($q !== '', function ($query) use ($q): void {
                $query->where(function ($inner) use ($q): void {
                    $inner->where('name', 'like', "%{$q}%")
                        ->orWhere('project_code', 'like', "%{$q}%")
                        ->orWhere('description', 'like', "%{$q}%");
                });
            })
            ->when($teamId > 0, fn ($query) => $query->where('team_id', $teamId))
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $attributes): Project
    {
        return Project::query()->create($attributes);
    }

    public function update(Project $project, array $attributes): void
    {
        $project->update($attributes);
    }

    public function delete(Project $project): void
    {
        $project->delete();
    }

    /** @return Collection<int, Team> */
    public function listTeams(): Collection
    {
        return Team::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'code']);
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

    public function withMembersAndTasks(Project $project): Project
    {
        return $project->load([
            'team:id,name,code',
            'manager:id,employee_code,first_name,last_name',
            'members:id,employee_code,first_name,last_name',
            'tasks:id,project_id,title,status,priority,assigned_to_employee_id,due_date,progress_percent',
            'tasks.assignee:id,employee_code,first_name,last_name',
        ]);
    }
}
