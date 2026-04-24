@extends('layouts.backend')

@section('content')
<div class="wrapper-page">
    <div class="page-title d-flex justify-content-between align-items-center">
        <h1><i class="icon-bell"></i> Notices & Announcements</h1>
        @if($canCreate)
            <a href="{{ route('announcements.create') }}" class="btn btn-custom"><i class="icon-plus"></i> Add Notice/Announcement</a>
        @endif
    </div>

    @include('partials.flash')

    <div class="page-content">
        <div class="container-fluid">
            <div class="card no-border">
                <div class="content_wrapper" style="padding:20px;">
                    <div class="notice-page-meta d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                        <div class="notice-meta-chip">
                            <i class="icon-list"></i>
                            <span>Total: {{ $announcements->total() }}</span>
                        </div>
                        <div class="notice-meta-chip">
                            <i class="icon-clock"></i>
                            <span>Ordered latest first</span>
                        </div>
                    </div>

                    <form method="GET" class="row g-2 mb-3">
                        <div class="col-md-4">
                            <input type="text" name="q" value="{{ $filters['q'] }}" class="form-control" placeholder="Search title or content">
                        </div>
                        <div class="col-md-2">
                            <select name="type" class="form-control">
                                <option value="">All Types</option>
                                <option value="notice" {{ $filters['type'] === 'notice' ? 'selected' : '' }}>Notice</option>
                                <option value="announcement" {{ $filters['type'] === 'announcement' ? 'selected' : '' }}>Announcement</option>
                            </select>
                        </div>
                        @if($canManageStatuses)
                            <div class="col-md-2">
                                <select name="status" class="form-control">
                                    <option value="">All Status</option>
                                    <option value="pending" {{ $filters['status'] === 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="approved" {{ $filters['status'] === 'approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="rejected" {{ $filters['status'] === 'rejected' ? 'selected' : '' }}>Rejected</option>
                                    <option value="published" {{ $filters['status'] === 'published' ? 'selected' : '' }}>Published</option>
                                    <option value="expired" {{ $filters['status'] === 'expired' ? 'selected' : '' }}>Expired</option>
                                </select>
                            </div>
                        @endif
                        <div class="col-md-2">
                            <select name="per_page" class="form-control">
                                @foreach([10,20,50,100] as $size)
                                    <option value="{{ $size }}" {{ (int) $filters['per_page'] === $size ? 'selected' : '' }}>{{ $size }} / page</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-{{ $canManageStatuses ? '2' : '4' }} d-flex gap-2">
                            <button class="btn btn-custom" type="submit"><i class="icon-magnifier"></i> Filter</button>
                            <a href="{{ route('announcements.index') }}" class="btn btn-custom-default"><i class="icon-refresh"></i> Reset</a>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-bordered align-middle notice-table">
                            <thead>
                                <tr>
                                    <th><i class="icon-doc me-1"></i> Title</th>
                                    <th><i class="icon-tag me-1"></i> Type</th>
                                    <th><i class="icon-flag me-1"></i> Priority</th>
                                    <th><i class="icon-layers me-1"></i> Status</th>
                                    <th><i class="icon-user me-1"></i> Created By</th>
                                    <th><i class="icon-clock me-1"></i> Published At</th>
                                    <th><i class="icon-settings me-1"></i> Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($announcements as $announcement)
                                    @php($status = $announcement->workflowStatus())
                                    <tr>
                                        <td>
                                            <strong>{{ $announcement->title }}</strong>
                                        </td>
                                        <td>
                                            <span class="badge {{ $announcement->announcement_type === 'notice' ? 'bg-info' : 'bg-primary' }}">
                                                <i class="{{ $announcement->announcement_type === 'notice' ? 'icon-bell' : 'icon-doc' }}"></i>
                                                {{ ucfirst($announcement->announcement_type) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge {{ $announcement->priority === 'high' ? 'bg-danger' : 'bg-secondary' }}">
                                                {{ ucfirst($announcement->priority) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($status === 'published')
                                                <span class="badge bg-success">Published</span>
                                            @elseif($status === 'approved')
                                                <span class="badge bg-info">Approved</span>
                                            @elseif($status === 'rejected')
                                                <span class="badge bg-danger">Rejected</span>
                                            @else
                                                <span class="badge bg-warning text-dark">Pending</span>
                                            @endif
                                            @if($announcement->isExpired())
                                                <span class="badge bg-dark">Expired</span>
                                            @endif
                                        </td>
                                        <td>{{ $announcement->creator?->name ?? 'System' }}</td>
                                        <td>{{ $announcement->publish_at?->format('Y-m-d H:i') ?? '-' }}</td>
                                        <td class="action-buttons">
                                            <a href="{{ route('announcements.show', $announcement) }}" title="Details"><i class="icon-eye"></i></a>

                                            @if($canApprove && $announcement->approval_status !== 'approved')
                                                <form method="POST" action="{{ route('announcements.approve', $announcement) }}" style="display:inline;" onsubmit="return confirm('Approve this item?');">
                                                    @csrf
                                                    <button type="submit" title="Approve"><i class="icon-check"></i></button>
                                                </form>
                                            @endif

                                            @if($canPublish && $announcement->publish_at === null)
                                                <form method="POST" action="{{ route('announcements.publish', $announcement) }}" style="display:inline;" onsubmit="return confirm('Publish this item?');">
                                                    @csrf
                                                    <button type="submit" title="Publish"><i class="icon-bell"></i></button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">No notices/announcements found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $announcements->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>

</style>
@endpush
