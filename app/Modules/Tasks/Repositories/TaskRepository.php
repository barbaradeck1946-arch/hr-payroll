<?php

namespace App\Modules\Tasks\Repositories;

use App\Models\Employee;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskComment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class TaskRepository
{
    /**
     * @param array<string, mixed> $filters
     */
    public function paginate(array $filters): LengthAwarePaginator
    {
        $q = trim((string) ($filters['q'] ?? ''));
        $projectId = (int) ($filters['project_id'] ?? 0);
        $status = (string) ($filters['status'] ?? '');
        $assigneeId = (int) ($filters['assigned_to_employee_id'] ?? 0);
        $perPage = max(10, min(100, (int) ($filters['per_page'] ?? 20)));

        return Task::query()
            ->with([
                'project:id,name,project_code',
                'assignee:id,employee_code,first_name,last_name',
                'creator:id,employee_code,first_name,last_name',
            ])
            ->when($q !== '', fn ($query) => $query->where('title', 'like', "%{$q}%"))
            ->when($projectId > 0, fn ($query) => $query->where('project_id', $projectId))
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($assigneeId > 0, fn ($query) => $query->where('assigned_to_employee_id', $assigneeId))
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $attributes): Task
    {
        return Task::query()->create($attributes);
    }

    public function update(Task $task, array $attributes): void
    {
        $task->update($attributes);
    }

    public function delete(Task $task): void
    {
        $task->delete();
    }

    /** @return Collection<int, Project> */
    public function listProjects(): Collection
    {
        return Project::query()->orderBy('name')->get(['id', 'name', 'project_code']);
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

    public function withComments(Task $task): Task
    {
        return $task->load([
            'project:id,name,project_code',
            'assignee:id,employee_code,first_name,last_name',
            'creator:id,employee_code,first_name,last_name',
            'comments:id,task_id,employee_id,comment,created_at',
            'comments.employee:id,employee_code,first_name,last_name',
        ]);
    }

    public function addComment(int $taskId, ?int $employeeId, string $comment): TaskComment
    {
        return TaskComment::query()->create([
            'task_id' => $taskId,
            'employee_id' => $employeeId,
            'comment' => $comment,
        ]);
    }
}
