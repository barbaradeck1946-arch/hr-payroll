@extends('layouts.backend')

@section('content')
@php
    $perms = $dashboardPermissions ?? [];
    $can = fn (string $permission): bool => $perms[$permission] ?? false;
    $scopeLabel = match ($dashboardScope ?? 'self') {
        'all' => 'Company overview',
        'department' => 'Department overview',
        default => 'Personal overview',
    };
    $visibleCards = collect($summaryCards ?? [])->filter(fn ($card) => $can($card['permission'] ?? ''))->values();
    $attendance = $attendanceSummary ?? ['present' => 0, 'absent' => 0, 'late' => 0, 'leave' => 0];
    $departmentRows = collect($departmentChart ?? []);
@endphp

<div class="wrapper-page">
    <div class="page-title">
        <h1><i class="icon-grid"></i> Dashboard</h1>
    </div>

    @include('partials.flash')

    <div class="page-content">
        <div class="container-fluid hr-dashboard">
            <div class="dashboard-hero">
                <div>
                    <span class="dashboard-kicker">HR dashboard</span>
                    <h2>{{ $scopeLabel }}</h2>
                    <p>Basic employee visibility, daily attendance, notices, notes, and upcoming events.</p>
                </div>
                <div class="dashboard-hero-date">
                    <span>{{ now()->format('l') }}</span>
                    <strong>{{ now()->format('M d, Y') }}</strong>
                </div>
            </div>

            @if($visibleCards->isNotEmpty())
                <div class="dashboard-card-grid">
                    @foreach($visibleCards as $card)
                        <div class="dashboard-stat dashboard-stat-{{ $card['tone'] ?? 'neutral' }}">
                            <div>
                                <span>{{ $card['label'] }}</span>
                                <strong>{{ is_float($card['value']) ? number_format($card['value'], 1) : number_format((int) $card['value']) }}</strong>
                            </div>
                            <i class="{{ $card['icon'] }}"></i>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="row g-3 align-items-start">
                <div class="col-xl-8">
                    <div class="row g-3">
                        @if($can('dashboard.attendance_chart'))
                            <div class="{{ $can('dashboard.department_chart') && ($dashboardScope ?? 'self') !== 'self' ? 'col-lg-6' : 'col-12' }}">
                                <div class="dashboard-panel">
                                    <div class="dashboard-panel-header">
                                        <div>
                                            <h3>{{ ($dashboardScope ?? 'self') === 'self' ? 'My Monthly Attendance Summary' : 'Attendance Summary' }}</h3>
                                            <p>Present, absent, late, and leave counts.</p>
                                        </div>
                                    </div>
                                    <div class="dashboard-chart-wrap">
                                        <canvas
                                            id="attendanceSummaryChart"
                                            data-present="{{ (int) ($attendance['present'] ?? 0) }}"
                                            data-absent="{{ (int) ($attendance['absent'] ?? 0) }}"
                                            data-late="{{ (int) ($attendance['late'] ?? 0) }}"
                                            data-leave="{{ (int) ($attendance['leave'] ?? 0) }}"
                                        ></canvas>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($can('dashboard.department_chart') && ($dashboardScope ?? 'self') !== 'self')
                            <div class="col-lg-6">
                                <div class="dashboard-panel">
                                    <div class="dashboard-panel-header">
                                        <div>
                                            <h3>Department-wise Employees</h3>
                                            <p>Active employee count by department.</p>
                                        </div>
                                    </div>
                                    <div class="dashboard-chart-wrap">
                                        <canvas
                                            id="departmentEmployeeChart"
                                            data-labels="{{ $departmentRows->pluck('label')->values()->toJson() }}"
                                            data-values="{{ $departmentRows->pluck('value')->map(fn ($value) => (int) $value)->values()->toJson() }}"
                                        ></canvas>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($can('dashboard.today_attendance_table'))
                            <div class="col-12">
                                <div class="dashboard-panel">
                                    <div class="dashboard-panel-header">
                                        <div>
                                            <h3>{{ ($dashboardScope ?? 'self') === 'self' ? 'My Recent Attendance' : (($dashboardScope ?? 'self') === 'department' ? 'Today Team Attendance' : 'Today Attendance') }}</h3>
                                            <p>Latest attendance entries within your dashboard scope.</p>
                                        </div>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table dashboard-table">
                                            <thead>
                                                <tr>
                                                    <th>Employee</th>
                                                    <th>Department</th>
                                                    <th>Status</th>
                                                    <th>Check In</th>
                                                    <th>Check Out</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse(($todayAttendanceRows ?? collect()) as $row)
                                                    <tr>
                                                        <td>
                                                            <strong>{{ trim($row->employee?->first_name . ' ' . $row->employee?->last_name) ?: '-' }}</strong>
                                                            <div class="small text-muted">{{ $row->employee?->employee_code }}</div>
                                                        </td>
                                                        <td>{{ $row->employee?->department?->name ?? 'Unassigned' }}</td>
                                                        <td><span class="dashboard-status status-{{ $row->status }}">{{ ucfirst($row->status) }}</span></td>
                                                        <td>{{ $row->check_in_at?->format('h:i A') ?? '-' }}</td>
                                                        <td>{{ $row->check_out_at?->format('h:i A') ?? '-' }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="5" class="text-center text-muted">No attendance entries available.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="col-lg-6">
                            @if($can('dashboard.pending_leave_table'))
                                <div class="dashboard-panel">
                                    <div class="dashboard-panel-header">
                                        <div>
                                            <h3>{{ ($dashboardScope ?? 'self') === 'self' ? 'My Leave Requests' : (($dashboardScope ?? 'self') === 'department' ? 'Team Pending Leave Requests' : 'Pending Leave Requests') }}</h3>
                                            <p>Open leave requests for this dashboard scope.</p>
                                        </div>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table dashboard-table">
                                            <thead>
                                                <tr>
                                                    <th>Employee</th>
                                                    <th>Leave</th>
                                                    <th>Dates</th>
                                                    <th>Days</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse(($pendingLeaveRows ?? collect()) as $leave)
                                                    <tr>
                                                        <td>{{ trim($leave->employee?->first_name . ' ' . $leave->employee?->last_name) ?: '-' }}</td>
                                                        <td>{{ $leave->leaveCategory?->name ?? '-' }}</td>
                                                        <td>{{ $leave->start_date ? \Illuminate\Support\Carbon::parse($leave->start_date)->format('M d') : '-' }} - {{ $leave->end_date ? \Illuminate\Support\Carbon::parse($leave->end_date)->format('M d') : '-' }}</td>
                                                        <td>{{ number_format((float) $leave->total_days, 1) }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="4" class="text-center text-muted">No pending leave requests.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="col-lg-6">
                            @if($can('dashboard.upcoming_events_table'))
                                <div class="dashboard-panel">
                                    <div class="dashboard-panel-header">
                                        <div>
                                            <h3>{{ ($dashboardScope ?? 'self') === 'department' ? 'Upcoming Team Events' : 'Upcoming Holidays & Birthdays' }}</h3>
                                            <p>Events in the next 45 days.</p>
                                        </div>
                                    </div>
                                    <div class="dashboard-event-list">
                                        @forelse(($upcomingEvents ?? collect()) as $event)
                                            <div class="dashboard-event">
                                                <span>{{ $event['type'] }}</span>
                                                <div>
                                                    <strong>{{ $event['title'] }}</strong>
                                                    <p>{{ $event['date']?->format('M d') ?? '-' }} · {{ $event['meta'] }}</p>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="dashboard-empty">No upcoming holidays or birthdays.</div>
                                        @endforelse
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-xl-4">
                    <div class="dashboard-side-stack">
                        @if($can('dashboard.notice_board'))
                            <div class="dashboard-panel">
                                <div class="dashboard-panel-header">
                                    <div>
                                        <h3>Notice Board</h3>
                                        <p>Published HR/Admin notices.</p>
                                    </div>
                                    <div class="d-flex gap-2">
                                        @if($canViewAnnouncements ?? false)
                                            <a href="{{ route('announcements.index') }}" class="btn btn-sm btn-outline-secondary">View</a>
                                        @endif
                                        @if($canCreateAnnouncement ?? false)
                                            <a href="{{ route('announcements.create') }}" class="btn btn-sm btn-primary">Add</a>
                                        @endif
                                    </div>
                                </div>
                                <div class="dashboard-notice-list">
                                    @forelse(($latestAnnouncements ?? collect()) as $item)
                                        <a href="{{ ($canViewAnnouncements ?? false) ? route('announcements.show', $item) : '#' }}" class="dashboard-notice">
                                            <span class="{{ $item->announcement_type === 'notice' ? 'notice' : 'announcement' }}">
                                                {{ ucfirst($item->announcement_type) }}
                                            </span>
                                            <strong>{{ $item->title }}</strong>
                                            <small>{{ $item->publish_at?->format('M d, Y') ?? 'Draft date unavailable' }}</small>
                                        </a>
                                    @empty
                                        <div class="dashboard-empty">No active notices available.</div>
                                    @endforelse
                                </div>
                            </div>
                        @endif

                        @if($can('dashboard.quick_notes'))
                            <div class="dashboard-panel">
                                <div class="dashboard-panel-header">
                                    <div>
                                        <h3>Quick Notes</h3>
                                        <p>Private notes for your own follow-up.</p>
                                    </div>
                                    <i class="icon-notebook dashboard-panel-icon"></i>
                                </div>
                                <ul
                                    class="dashboard-note-list"
                                    id="quick-note-list"
                                    data-csrf="{{ csrf_token() }}"
                                    data-can-update="{{ ($canUpdatePrivateNotes ?? false) ? '1' : '0' }}"
                                    data-can-delete="{{ ($canDeletePrivateNotes ?? false) ? '1' : '0' }}"
                                >
                                    @if(!($canViewPrivateNotes ?? false))
                                        <li class="todo-item quick-note-empty dashboard-empty">You do not have permission to view private notes.</li>
                                    @elseif(($privateNotes ?? collect())->isEmpty())
                                        <li class="todo-item quick-note-empty dashboard-empty">No notes yet. Add your first private note below.</li>
                                    @else
                                        @foreach(($privateNotes ?? collect()) as $note)
                                            @php($noteInputId = 'quick_note_' . $note->id)
                                            <li class="todo-item dashboard-note" data-note-id="{{ $note->id }}">
                                                <div class="d-flex align-items-start gap-2">
                                                    @if($canUpdatePrivateNotes ?? false)
                                                        <form method="POST" action="{{ route('dashboard.quick-notes.toggle', $note) }}" class="checkbox checkbox-default pt-1 quick-note-toggle-form">
                                                            @csrf
                                                            @method('PATCH')
                                                            <input class="to-do quick-note-toggle" type="checkbox" id="{{ $noteInputId }}" {{ $note->is_completed ? 'checked' : '' }}>
                                                            <label for="{{ $noteInputId }}"></label>
                                                        </form>
                                                    @endif
                                                    <div class="flex-grow-1">
                                                        <div class="fw-semibold quick-note-title {{ $note->is_completed ? 'text-decoration-line-through text-muted' : '' }}">{{ $note->title }}</div>
                                                        <div class="small quick-note-body {{ $note->is_completed ? 'text-decoration-line-through text-muted' : 'text-muted' }}">{{ $note->note_body }}</div>
                                                    </div>
                                                    @if($canUpdatePrivateNotes ?? false)
                                                        <button type="button" class="btn btn-sm btn-outline-secondary quick-note-edit-btn" title="Edit note" data-action="{{ route('dashboard.quick-notes.update', $note) }}">
                                                            <i class="icon-pencil"></i>
                                                        </button>
                                                    @endif
                                                    @if($canDeletePrivateNotes ?? false)
                                                        <form method="POST" action="{{ route('dashboard.quick-notes.delete', $note) }}" class="quick-note-delete-form">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete note"><i class="icon-trash"></i></button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </li>
                                        @endforeach
                                    @endif
                                </ul>
                                @if($canCreatePrivateNotes ?? false)
                                    <form method="POST" action="{{ route('dashboard.quick-notes.store') }}" id="add_todo" class="quick-note-add-form dashboard-note-form">
                                        @csrf
                                        <div class="input-group">
                                            <input type="text" name="note_body" class="form-control" placeholder="Add private note" required maxlength="2000">
                                            <button type="submit" class="btn btn-primary"><i class="fa fa-plus"></i></button>
                                        </div>
                                    </form>
                                @endif
                            </div>
                        @endif

                        @if($can('dashboard.basic_alerts'))
                            <div class="dashboard-panel">
                                <div class="dashboard-panel-header">
                                    <div>
                                        <h3>Basic Alerts</h3>
                                        <p>Simple items needing attention.</p>
                                    </div>
                                </div>
                                <div class="dashboard-alert-list">
                                    @forelse(($basicAlerts ?? []) as $alert)
                                        <div class="dashboard-alert alert-{{ $alert['tone'] }}">
                                            <i class="icon-info"></i>
                                            <span>{{ $alert['label'] }}</span>
                                        </div>
                                    @empty
                                        <div class="dashboard-empty">No alerts right now.</div>
                                    @endforelse
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .hr-dashboard {
        display: grid;
        gap: 18px;
    }

    .dashboard-hero,
    .dashboard-panel,
    .dashboard-stat {
        border: 1px solid var(--hr-border);
        border-radius: var(--hr-radius);
        background: var(--hr-surface);
        box-shadow: var(--hr-shadow-soft);
    }

    .dashboard-hero {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 20px 22px;
    }

    .dashboard-kicker {
        display: inline-flex;
        margin-bottom: 6px;
        color: var(--hr-accent);
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .dashboard-hero h2 {
        margin: 0 0 4px;
        font-size: 22px;
        font-weight: 800;
    }

    .dashboard-hero p,
    .dashboard-panel-header p,
    .dashboard-event p {
        margin: 0;
        color: var(--hr-muted);
        font-size: 13px;
    }

    .dashboard-hero-date {
        min-width: 150px;
        text-align: right;
    }

    .dashboard-hero-date span {
        display: block;
        color: var(--hr-muted);
        font-size: 12px;
    }

    .dashboard-hero-date strong {
        color: var(--hr-text);
        font-size: 16px;
    }

    .dashboard-card-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
    }

    .dashboard-stat {
        display: flex;
        align-items: center;
        justify-content: space-between;
        min-height: 104px;
        padding: 18px;
        overflow: hidden;
    }

    .dashboard-stat span {
        display: block;
        color: var(--hr-muted);
        font-size: 13px;
        font-weight: 700;
    }

    .dashboard-stat strong {
        display: block;
        margin-top: 8px;
        color: var(--hr-text);
        font-size: 28px;
        font-weight: 800;
        line-height: 1;
    }

    .dashboard-stat i {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 44px;
        height: 44px;
        border-radius: var(--hr-radius);
        font-size: 20px;
    }

    .dashboard-stat-primary i { color: #1b6c9e; background: #e6f2fb; }
    .dashboard-stat-success i { color: #127153; background: #dff4eb; }
    .dashboard-stat-danger i { color: #9f2828; background: #f5dcdc; }
    .dashboard-stat-warning i { color: #975f06; background: #fde9c4; }
    .dashboard-stat-info i { color: #0f6685; background: #def3fa; }
    .dashboard-stat-neutral i { color: var(--hr-primary); background: #edf2f7; }

    .dashboard-panel {
        overflow: hidden;
    }

    .dashboard-panel-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        padding: 16px 18px;
        border-bottom: 1px solid var(--hr-border);
        background: var(--hr-surface-subtle);
    }

    .dashboard-panel-header h3 {
        margin: 0 0 3px;
        font-size: 15px;
        font-weight: 800;
    }

    .dashboard-panel-icon {
        color: var(--hr-accent);
        font-size: 20px;
    }

    .dashboard-chart-wrap {
        height: 270px;
        padding: 18px;
    }

    .dashboard-table thead th {
        white-space: nowrap;
    }

    .dashboard-status {
        display: inline-flex;
        padding: 5px 9px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 800;
    }

    .status-present { color: #127153; background: #dff4eb; }
    .status-late { color: #975f06; background: #fde9c4; }
    .status-absent { color: #9f2828; background: #f5dcdc; }

    .dashboard-side-stack {
        display: grid;
        gap: 14px;
    }

    .dashboard-notice-list,
    .dashboard-event-list,
    .dashboard-alert-list,
    .dashboard-note-list {
        display: grid;
        gap: 10px;
        margin: 0;
        padding: 14px;
        list-style: none;
    }

    .dashboard-notice,
    .dashboard-event,
    .dashboard-alert,
    .dashboard-note {
        display: flex;
        gap: 10px;
        padding: 12px;
        border: 1px solid var(--hr-border);
        border-radius: var(--hr-radius-sm);
        background: #fff;
    }

    .dashboard-notice {
        flex-direction: column;
        color: var(--hr-text);
    }

    .dashboard-notice span,
    .dashboard-event span {
        width: max-content;
        padding: 4px 8px;
        border-radius: 999px;
        color: var(--hr-accent);
        background: var(--hr-accent-soft);
        font-size: 11px;
        font-weight: 800;
    }

    .dashboard-notice small {
        color: var(--hr-muted);
    }

    .dashboard-event {
        align-items: flex-start;
    }

    .dashboard-alert {
        align-items: center;
    }

    .dashboard-alert i {
        color: var(--hr-accent);
    }

    .alert-danger { border-color: #f0bcbc; background: #fff7f7; }
    .alert-warning { border-color: #efd79d; background: #fffaf0; }
    .alert-info { border-color: #bfe4ef; background: #f2fbfd; }
    .alert-neutral { background: #f8fafc; }

    .dashboard-note-form {
        padding: 0 14px 14px;
    }

    .dashboard-empty {
        padding: 14px;
        color: var(--hr-muted);
        border: 1px dashed var(--hr-border-strong);
        border-radius: var(--hr-radius-sm);
        background: var(--hr-surface-subtle);
        font-size: 13px;
    }

    @media (max-width: 1199px) {
        .dashboard-card-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 575px) {
        .dashboard-hero {
            align-items: flex-start;
            flex-direction: column;
        }

        .dashboard-hero-date {
            text-align: left;
        }

        .dashboard-card-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@push('scripts')
<script>
(function () {
    var attendanceCanvas = document.getElementById('attendanceSummaryChart');
    if (attendanceCanvas && window.Chart) {
        new Chart(attendanceCanvas.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Present', 'Absent', 'Late', 'On Leave'],
                datasets: [{
                    data: [
                        Number(attendanceCanvas.dataset.present || 0),
                        Number(attendanceCanvas.dataset.absent || 0),
                        Number(attendanceCanvas.dataset.late || 0),
                        Number(attendanceCanvas.dataset.leave || 0)
                    ],
                    backgroundColor: ['#1f9d72', '#c24141', '#d08813', '#0f8f8c'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: { position: 'bottom' },
                cutoutPercentage: 62
            }
        });
    }

    var departmentCanvas = document.getElementById('departmentEmployeeChart');
    if (departmentCanvas && window.Chart) {
        new Chart(departmentCanvas.getContext('2d'), {
            type: 'bar',
            data: {
                labels: JSON.parse(departmentCanvas.dataset.labels || '[]'),
                datasets: [{
                    label: 'Employees',
                    data: JSON.parse(departmentCanvas.dataset.values || '[]'),
                    backgroundColor: '#0f8f8c',
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: { display: false },
                scales: {
                    yAxes: [{ ticks: { beginAtZero: true, precision: 0 } }]
                }
            }
        });
    }
})();

(function () {
    var list = document.getElementById('quick-note-list');
    var addForm = document.querySelector('.quick-note-add-form');
    var csrf = list ? (list.getAttribute('data-csrf') || '') : '';
    var canUpdate = list ? list.getAttribute('data-can-update') === '1' : false;
    var canDelete = list ? list.getAttribute('data-can-delete') === '1' : false;

    function fetchForm(form) {
        return fetch(form.action, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: new FormData(form)
        }).then(function (res) { return res.json(); });
    }

    function fetchAction(url, payload) {
        var formData = new FormData();
        Object.keys(payload).forEach(function (key) {
            formData.append(key, payload[key]);
        });
        return fetch(url, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: formData
        }).then(function (res) { return res.json(); });
    }

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function ensureEmptyState() {
        if (!list) return;
        var items = list.querySelectorAll('.todo-item:not(.quick-note-empty)');
        var empty = list.querySelector('.quick-note-empty');
        if (items.length === 0 && !empty) {
            var emptyLi = document.createElement('li');
            emptyLi.className = 'todo-item quick-note-empty dashboard-empty';
            emptyLi.innerHTML = 'No notes yet. Add your first private note below.';
            list.appendChild(emptyLi);
        }
        if (items.length > 0 && empty) {
            empty.remove();
        }
    }

    function makeRowHtml(id, title, body) {
        var html = '<div class="d-flex align-items-start gap-2">';
        if (canUpdate) {
            html += '<form method="POST" action="/dashboard/quick-notes/' + id + '/toggle" class="checkbox checkbox-default pt-1 quick-note-toggle-form">' +
                '<input type="hidden" name="_token" value="' + csrf + '">' +
                '<input type="hidden" name="_method" value="PATCH">' +
                '<input class="to-do quick-note-toggle" type="checkbox" id="quick_note_' + id + '">' +
                '<label for="quick_note_' + id + '"></label>' +
                '</form>';
        }
        html += '<div class="flex-grow-1">' +
            '<div class="fw-semibold quick-note-title">' + title + '</div>' +
            '<div class="small quick-note-body text-muted">' + body + '</div>' +
            '</div>';
        if (canUpdate) {
            html += '<button type="button" class="btn btn-sm btn-outline-secondary quick-note-edit-btn" title="Edit note" data-action="/dashboard/quick-notes/' + id + '">' +
                '<i class="icon-pencil"></i>' +
                '</button>';
        }
        if (canDelete) {
            html += '<form method="POST" action="/dashboard/quick-notes/' + id + '" class="quick-note-delete-form">' +
                '<input type="hidden" name="_token" value="' + csrf + '">' +
                '<input type="hidden" name="_method" value="DELETE">' +
                '<button type="submit" class="btn btn-sm btn-outline-danger" title="Delete note"><i class="icon-trash"></i></button>' +
                '</form>';
        }
        return html + '</div>';
    }

    if (addForm) {
        addForm.addEventListener('submit', function (event) {
            event.preventDefault();
            fetchForm(addForm).then(function (json) {
                if (!json || !json.ok || !json.note || !list) return;
                list.querySelectorAll('.quick-note-empty').forEach(function (el) { el.remove(); });
                var row = document.createElement('li');
                row.className = 'todo-item dashboard-note';
                row.setAttribute('data-note-id', String(json.note.id));
                row.innerHTML = makeRowHtml(Number(json.note.id), escapeHtml(json.note.title || ''), escapeHtml(json.note.note_body || ''));
                list.prepend(row);
                ensureEmptyState();
                addForm.reset();
            }).catch(function () {});
        });
    }

    document.addEventListener('change', function (event) {
        var checkbox = event.target.closest('.quick-note-toggle');
        if (!checkbox) return;
        var form = checkbox.closest('.quick-note-toggle-form');
        var item = checkbox.closest('.todo-item');
        if (!form || !item) return;

        fetchForm(form).then(function (json) {
            if (!json || !json.ok || !json.note) return;
            var done = Boolean(json.note.is_completed);
            var title = item.querySelector('.quick-note-title');
            var body = item.querySelector('.quick-note-body');
            if (title) {
                title.classList.toggle('text-decoration-line-through', done);
                title.classList.toggle('text-muted', done);
            }
            if (body) {
                body.classList.toggle('text-decoration-line-through', done);
                body.classList.add('text-muted');
            }
        }).catch(function () {
            checkbox.checked = !checkbox.checked;
        });
    });

    document.addEventListener('click', function (event) {
        var button = event.target.closest('.quick-note-edit-btn');
        if (!button) return;
        var item = button.closest('.todo-item');
        if (!item) return;
        var bodyNode = item.querySelector('.quick-note-body');
        var titleNode = item.querySelector('.quick-note-title');
        var nextBody = window.prompt('Edit note', bodyNode ? (bodyNode.textContent || '').trim() : '');
        if (nextBody === null || nextBody.trim() === '') return;

        fetchAction(button.getAttribute('data-action') || '', {
            _token: csrf,
            _method: 'PATCH',
            title: titleNode ? (titleNode.textContent || '').trim() : '',
            note_body: nextBody.trim()
        }).then(function (json) {
            if (!json || !json.ok || !json.note) return;
            if (titleNode) titleNode.textContent = json.note.title || '';
            if (bodyNode) bodyNode.textContent = json.note.note_body || '';
        }).catch(function () {});
    });

    document.addEventListener('submit', function (event) {
        var form = event.target.closest('.quick-note-delete-form');
        if (!form) return;
        event.preventDefault();
        if (!window.confirm('Delete this note permanently?')) return;
        fetchForm(form).then(function (json) {
            if (!json || !json.ok) return;
            var item = form.closest('.todo-item');
            if (item) item.remove();
            ensureEmptyState();
        }).catch(function () {});
    });

    ensureEmptyState();
})();
</script>
@endpush
