@extends('layouts.backend')

@section('content')
<div class="wrapper-page">
    <div class="page-title">
        <h1><i class="icon-chart"></i> Reports</h1>
    </div>

    @include('partials.flash')

    <div class="page-content">
        <div class="container-fluid">
            <div class="row g-3">
                @if(auth()->user()?->hasAnyPermission(['report.employee', 'report.view', 'employee.view']))
                    <div class="col-md-3">
                        <a href="{{ route('reports.employees') }}" class="card no-border text-decoration-none">
                            <div class="content_wrapper content-padded">
                                <h5 class="table_banner_title mb-1">Employee Report</h5>
                                <p class="text-muted mb-0">Employee list, departments, designations and status.</p>
                            </div>
                        </a>
                    </div>
                @endif
                @if(auth()->user()?->hasAnyPermission(['report.attendance', 'report.view', 'attendance.report', 'attendance.view', 'attendance.manage']))
                    <div class="col-md-3">
                        <a href="{{ route('reports.attendance') }}" class="card no-border text-decoration-none">
                            <div class="content_wrapper content-padded">
                                <h5 class="table_banner_title mb-1">Attendance Report</h5>
                                <p class="text-muted mb-0">Daily attendance, status and worked minutes.</p>
                            </div>
                        </a>
                    </div>
                @endif
                @if(auth()->user()?->hasAnyPermission(['report.leave', 'report.view', 'leave.report', 'leave.approve', 'leave.view']))
                    <div class="col-md-3">
                        <a href="{{ route('leave-reports.index') }}" class="card no-border text-decoration-none">
                            <div class="content_wrapper content-padded">
                                <h5 class="table_banner_title mb-1">Leave Report</h5>
                                <p class="text-muted mb-0">Leave applications, categories, days and approval status.</p>
                            </div>
                        </a>
                    </div>
                @endif
                @if(auth()->user()?->hasAnyPermission(['report.payroll', 'report.view', 'payroll.report', 'payslip.view']))
                    <div class="col-md-3">
                        <a href="{{ route('reports.payroll') }}" class="card no-border text-decoration-none">
                            <div class="content_wrapper content-padded">
                                <h5 class="table_banner_title mb-1">Payroll Report</h5>
                                <p class="text-muted mb-0">Payroll items, gross, deductions and net payable.</p>
                            </div>
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
