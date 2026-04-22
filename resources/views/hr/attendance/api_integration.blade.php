@extends('layouts.backend')
@section('title', 'Attendance API Integration')


@section('content')
<div class="wrapper-page">
    <div class="page-title d-flex justify-content-between align-items-center">
        <h1><i class="icon-link"></i> Attendance API Integration</h1>
        <a href="{{ route('attendance.index') }}" class="btn btn-custom-default"><i class="icon-arrow-left"></i> Back</a>
    </div>

    @include('partials.flash')

    <div class="page-content">
        <div class="container-fluid">
            @if($latestPlainToken)
                <div class="alert alert-warning">
                            <strong>New API Token (copy now):</strong>
                        <div style="word-break:break-all;">{{ $latestPlainToken }}</div>
                    <small>This token will not be shown again.</small>
                </div>
            @endif

            @if($canManageAttendance)
                <div class="card no-border mb-3">
                    <div class="content_wrapper" style="padding:20px;">
                        <h5 class="table_banner_title mb-3">Create API Client</h5>
                        <form method="POST" action="{{ route('attendance.api-clients.store') }}" class="row g-2">
                            @csrf
                            <div class="col-md-4">
                                <label>Client Name</label>
                                        <input type="text" name="name" class="form-control" required placeholder="Device/Provider name">
                                    </div>
                            <div class="col-md-6">
                                <label>Allowed IPs (optional)</label>
                                        <input type="text" name="allowed_ips" class="form-control" placeholder="e.g. 203.0.113.10,198.51.100.25">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-custom"><i class="icon-plus"></i> Generate Token</button>
                                </div>
                        </form>
                    </div>
                </div>
            @endif

            <div class="card no-border mb-3">
                <div class="content_wrapper" style="padding:20px;">
                    <h5 class="table_banner_title mb-3">Endpoint</h5>
                    <pre class="mb-2 api-endpoint-block"><code>POST /api/v1/attendance/logs/bulk</code></pre>
                    <h6>Headers</h6>
                    <pre class="mb-2 api-endpoint-block"><code>Authorization: Bearer YOUR_API_TOKEN
Content-Type: application/json</code></pre>
                    <h6>Payload Example</h6>
                    <pre class="mb-0 api-json-block"><code>{
  "entries": [
    {
      "employee_code": "EMP0001",
      "attendance_date": "2026-03-01",
      "entry_type": "checkin",
      "entry_time": "09:01 AM",
      "remarks": "Device punch in"
    },
    {
      "employee_code": "EMP0001",
      "attendance_date": "2026-03-01",
      "entry_type": "checkout",
      "entry_time": "06:05 PM",
      "remarks": "Device punch out"
    }
  ]
}</code></pre>
                </div>
            </div>

            <div class="card no-border">
                <div class="content_wrapper" style="padding:20px;">
                    <h5 class="table_banner_title mb-3">API Clients</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Allowed IPs</th>
                                    <th>Status</th>
                                    <th>Last Used</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($apiClients as $client)
                                    <tr>
                                        <td>{{ $client->name }}</td>
                                        <td>{{ $client->allowed_ips ?: '-' }}</td>
                                        <td>{{ $client->is_active ? 'Active' : 'Disabled' }}</td>
                                        <td>{{ $client->last_used_at ? $client->last_used_at->format('Y-m-d H:i:s') : '-' }}</td>
                                        <td>
                                            @if($canManageAttendance)
                                                <form method="POST" action="{{ route('attendance.api-clients.toggle', $client) }}" style="display:inline;">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button class="btn btn-custom-default btn-sm" type="submit">
                                                        {{ $client->is_active ? 'Disable' : 'Enable' }}
                                                    </button>
                                                </form>
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">No API clients configured.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .api-endpoint-block {
        background: #1f2733;
        color: #f3f7fb;
        border-radius: 8px;
        padding: 12px 14px;
    }
    .api-json-block {
        background: #121a13;
        color: #8ee39d;
        border-radius: 8px;
        padding: 12px 14px;
    }
    .api-endpoint-block code,
    .api-json-block code {
        color: inherit;
        font-size: 13px;
        line-height: 1.55;
    }
</style>
@endpush
