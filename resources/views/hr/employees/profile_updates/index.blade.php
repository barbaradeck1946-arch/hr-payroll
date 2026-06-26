@extends('layouts.backend')

@section('content')
<div class="wrapper-page">
    <div class="page-title d-flex justify-content-between align-items-center">
        <h1><i class="icon-layers"></i> Profile Update Requests</h1>
    </div>

    @include('partials.flash')

    <div class="page-content">
        <div class="container-fluid">
            <div class="card no-border">
                <div class="content_wrapper content-padded">
                    <form method="GET" class="row g-2 mb-3">
                        <div class="col-md-4">
                            <input type="text" name="q" value="{{ $filters['q'] }}" class="form-control" placeholder="Search by employee code or name">
                        </div>
                        <div class="col-md-3">
                            <select name="approval_status" class="form-control">
                                <option value="">All Status</option>
                                @foreach(['pending','approved','rejected'] as $status)
                                    <option value="{{ $status }}" {{ $filters['approval_status'] === $status ? 'selected' : '' }}>
                                        {{ ucfirst($status) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                                <select name="per_page" class="form-control">
                                    @foreach([10,20,50,100] as $size)
                                        <option value="{{ $size }}" {{ (int) $filters['per_page'] === $size ? 'selected' : '' }}>{{ $size }}</option>
                                    @endforeach
                                </select>
                        </div>
                            <div class="col-md-3 d-flex gap-2">
                                <button type="submit" class="btn btn-custom"><i class="icon-magnifier"></i> Filter</button>
                                <a href="{{ route('employees.profile-updates.index') }}" class="btn btn-custom-default"><i class="icon-refresh"></i> Reset</a>
                            </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Submitted By</th>
                                    <th>Submitted At</th>
                                    <th>Status</th>
                                    <th>Reviewed By</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($requests as $item)
                                    <tr>
                                            <td>
                                                {{ trim($item->employee?->first_name.' '.$item->employee?->last_name) }}
                                                <div class="small text-muted">{{ $item->employee?->employee_code }}</div>
                                            </td>
                                            <td>{{ $item->submittedBy?->name ?? '-' }}</td>
                                            <td>{{ $item->submitted_at?->format('Y-m-d H:i') ?? '-' }}</td>
                                            <td>{{ ucfirst($item->approval_status) }}</td>
                                            <td>{{ $item->reviewedBy?->name ?? '-' }}</td>
                                            <td class="action-buttons">
                                                <a href="{{ route('employees.profile-updates.show', $item) }}" title="View Request"><i class="icon-eye"></i></a>
                                            </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center">No profile update request found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{ $requests->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
