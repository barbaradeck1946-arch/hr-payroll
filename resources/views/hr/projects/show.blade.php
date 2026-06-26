@extends('layouts.backend')
@section('title', 'Project Details')

@section('content')
<div class="wrapper-page">
    <div class="page-title d-flex justify-content-between align-items-center">
        <h1><i class="icon-briefcase"></i> Project: {{ $project->name }}</h1>
        <div class="d-flex gap-2">
            @if(auth()->user()?->hasPermission('project.manage-members'))<a href="{{ route('projects.members', $project) }}" class="btn btn-custom-default">Members</a>@endif
            <a href="{{ route('projects.index') }}" class="btn btn-custom-default"><i class="icon-arrow-left"></i> Back</a>
        </div>
    </div>

    @include('partials.flash')

    <div class="page-content"><div class="container-fluid"><div class="row g-3">
        <div class="col-md-6"><div class="content_wrapper" style="padding:20px;">
            <h5 class="table_banner_title mb-3">Project Summary</h5>
            <p><strong>Code:</strong> {{ $project->project_code }}</p>
            <p><strong>Team:</strong> {{ $project->team?->name ?? '-' }}</p>
            <p><strong>Team Lead:</strong> {{ trim(($project->team?->lead?->first_name ?? '').' '.($project->team?->lead?->last_name ?? '')) ?: '-' }}</p>
            <p><strong>Manager:</strong> {{ trim(($project->manager?->first_name ?? '').' '.($project->manager?->last_name ?? '')) ?: '-' }}</p>
            <p><strong>Status:</strong> {{ ucfirst(str_replace('_', ' ', $project->status)) }}</p>
            <p><strong>Progress:</strong> {{ (int) $project->progress_percent }}%</p>
            <p><strong>Timeline:</strong> {{ $project->start_date ?? '-' }} to {{ $project->deadline ?? '-' }}</p>
            <p><strong>Description:</strong><br>{{ $project->description ?: '-' }}</p>
        </div></div>
        <div class="col-md-6"><div class="content_wrapper" style="padding:20px;">
            <h5 class="table_banner_title mb-3">Assigned Members</h5>
            @php
                $assignedRows = collect();

                if ($project->members->isNotEmpty()) {
                    $assignedRows = $project->members->map(fn($member) => [
                        'employee' => $member,
                        'role' => ucfirst($member->pivot->project_role ?? 'member'),
                    ]);
                }

                if ($assignedRows->isEmpty() && $project->team) {
                    if ($project->team->lead) {
                        $assignedRows->push([
                            'employee' => $project->team->lead,
                            'role' => 'Team Lead',
                        ]);
                    }

                    foreach ($project->team->members as $member) {
                        if ($assignedRows->contains(fn($row) => (int) $row['employee']->id === (int) $member->id)) {
                            continue;
                        }

                        $assignedRows->push([
                            'employee' => $member,
                            'role' => 'Team ' . ucfirst($member->pivot->member_role ?? 'member'),
                        ]);
                    }
                }

                if ($assignedRows->isEmpty()) {
                    $assignedRows = $project->tasks
                        ->pluck('assignee')
                        ->filter()
                        ->unique('id')
                        ->values()
                        ->map(fn($member) => [
                            'employee' => $member,
                            'role' => 'Task Assignee',
                        ]);
                }
            @endphp
            <ul class="mb-0">
                @forelse($assignedRows as $row)
                    @php($member = $row['employee'])
                    <li>{{ trim(($member->first_name ?? '').' '.($member->last_name ?? '')) }} ({{ $member->employee_code }}) - {{ $row['role'] }}</li>
                @empty
                    <li>No members assigned.</li>
                @endforelse
            </ul>
        </div></div>
        <div class="col-md-12"><div class="content_wrapper" style="padding:20px;">
            <h5 class="table_banner_title mb-3">Project Tasks</h5>
            <div class="table-responsive"><table class="table table-bordered align-middle"><thead><tr><th>Title</th><th>Assignee</th><th>Priority</th><th>Status</th><th>Due Date</th><th>Progress</th></tr></thead><tbody>
                @forelse($project->tasks as $task)
                    <tr>
                        <td><a href="{{ route('tasks.show', $task) }}">{{ $task->title }}</a></td>
                        <td>{{ trim(($task->assignee?->first_name ?? '').' '.($task->assignee?->last_name ?? '')) ?: '-' }}</td>
                        <td>{{ ucfirst($task->priority) }}</td>
                        <td>{{ ucfirst(str_replace('_',' ', $task->status)) }}</td>
                        <td>{{ $task->due_date ?? '-' }}</td>
                        <td>{{ (int) $task->progress_percent }}%</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center">No tasks added yet.</td></tr>
                @endforelse
            </tbody></table></div>
        </div></div>
    </div></div></div>
</div>
@endsection
