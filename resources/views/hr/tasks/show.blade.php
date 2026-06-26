@extends('layouts.backend')
@section('title', 'Task Details')

@section('content')
<div class="wrapper-page">
    <div class="page-title d-flex justify-content-between align-items-center">
        <h1><i class="icon-list"></i> Task: {{ $task->title }}</h1>
        <div class="d-flex gap-2">
            @if(auth()->user()?->hasPermission('task.update'))<a href="{{ route('tasks.edit', $task) }}" class="btn btn-custom">Edit</a>@endif
            <a href="{{ route('tasks.index') }}" class="btn btn-custom-default"><i class="icon-arrow-left"></i> Back</a>
        </div>
    </div>

    @include('partials.flash')

    <div class="page-content"><div class="container-fluid"><div class="row g-3">
        <div class="col-md-7"><div class="card no-border"><div class="content_wrapper content-padded">
            <h5 class="table_banner_title mb-3">Task Summary</h5>
            <p><strong>Project:</strong> {{ $task->project?->name ?? '-' }}</p>
            <p><strong>Assignee:</strong> {{ trim(($task->assignee?->first_name ?? '').' '.($task->assignee?->last_name ?? '')) ?: '-' }}</p>
            <p><strong>Priority:</strong> {{ ucfirst($task->priority) }}</p>
            <p><strong>Status:</strong> {{ ucfirst(str_replace('_', ' ', $task->status)) }}</p>
            <p><strong>Progress:</strong> {{ (int) $task->progress_percent }}%</p>
            <p><strong>Dates:</strong> {{ $task->start_date ?? '-' }} to {{ $task->due_date ?? '-' }}</p>
            <p><strong>Description:</strong><br>{{ $task->description ?: '-' }}</p>

            @if(auth()->user()?->hasPermission('task.update'))
            <hr>
            <h6>Update Status</h6>
            <form method="POST" action="{{ route('tasks.status.update', $task) }}" class="row g-2">
                @csrf
                @method('PATCH')
                <div class="col-md-4"><select name="status" class="form-control" required>@foreach(['todo','in_progress','review','done','blocked','cancelled'] as $status)<option value="{{ $status }}" {{ $task->status===$status?'selected':'' }}>{{ ucfirst(str_replace('_',' ',$status)) }}</option>@endforeach</select></div>
                <div class="col-md-3"><input type="number" min="0" max="100" name="progress_percent" value="{{ (int)$task->progress_percent }}" class="form-control" required></div>
                <div class="col-md-3"><button type="submit" class="btn btn-custom">Update</button></div>
            </form>
            @endif
        </div></div></div>

        <div class="col-md-5"><div class="card no-border"><div class="content_wrapper content-padded">
            <h5 class="table_banner_title mb-3">Comments</h5>
            <div class="scroll-panel-sm">
                @forelse($task->comments as $comment)
                    @php($name = trim(($comment->employee?->first_name ?? '').' '.($comment->employee?->last_name ?? '')))
                    <div class="border p-2 mb-2">
                        <div class="small text-muted">{{ $name !== '' ? $name : 'User' }} - {{ $comment->created_at?->format('Y-m-d H:i') }}</div>
                        <div>{{ $comment->comment }}</div>
                    </div>
                @empty
                    <div class="text-muted">No comments yet.</div>
                @endforelse
            </div>

            @if(auth()->user()?->hasPermission('task.comment'))
                <hr>
                <form method="POST" action="{{ route('tasks.comments.store', $task) }}">
                    @csrf
                    <textarea name="comment" class="form-control" rows="3" placeholder="Write comment" required></textarea>
                    <button type="submit" class="btn btn-custom mt-2">Add Comment</button>
                </form>
            @endif
        </div></div></div>
    </div></div></div>
</div>
@endsection
