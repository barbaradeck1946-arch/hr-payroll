@extends('layouts.backend')
@section('title', 'Employee Rejoin')

@section('content')
<div class="wrapper-page">
    <div class="page-title d-flex justify-content-between align-items-center">
        <h1><i class="icon-refresh"></i> Employee Rejoin</h1>
        <a href="{{ route('employee-statuses.index') }}" class="btn btn-custom-default"><i class="icon-arrow-left"></i> Back</a>
    </div>

    @include('partials.flash')

    <div class="page-content">
        <div class="container-fluid">
            <div class="card no-border">
                <div class="content_wrapper content-padded">
                    <h5 class="table_banner_title mb-3">
                        {{ trim(($employee->first_name ?? '') . ' ' . ($employee->last_name ?? '')) }} ({{ $employee->employee_code }})
                    </h5>

                    <div class="row mb-3">
                        <div class="col-md-4"><strong>Current Status:</strong> {{ ucwords(str_replace('_', ' ', $employee->employment_status)) }}</div>
                        <div class="col-md-4"><strong>Current Department:</strong> {{ $employee->department?->name ?? '-' }}</div>
                        <div class="col-md-4"><strong>Current Designation:</strong> {{ $employee->designation?->name ?? '-' }}</div>
                    </div>

                    <form method="POST" action="{{ route('employee-statuses.rejoin', $employee) }}" class="row g-2">
                        @csrf

                        <div class="col-md-4">
                            <label>Rejoin Date</label>
                            <input type="text" name="rejoin_date" class="form-control status-date-picker" value="{{ now()->toDateString() }}" placeholder="YYYY-MM-DD" required>
                        </div>

                        <div class="col-md-4">
                            <label>Department (optional)</label>
                            <select name="department_id" class="form-control">
                                <option value="">Keep Current</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label>Designation (optional)</label>
                            <select name="designation_id" class="form-control">
                                <option value="">Keep Current</option>
                                @foreach($designations as $designation)
                                    <option value="{{ $designation->id }}">{{ $designation->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label>Salary Grade (optional)</label>
                            <select name="salary_grade_id" class="form-control">
                                <option value="">Keep Current</option>
                                @foreach($salaryGrades as $salaryGrade)
                                    <option value="{{ $salaryGrade->id }}">{{ $salaryGrade->grade_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label>Reports To (optional)</label>
                            <select name="reports_to_id" class="form-control js-example-basic-single">
                                <option value="">Keep Current</option>
                                @foreach($managers as $manager)
                                    @php($managerName = trim(($manager->first_name ?? '') . ' ' . ($manager->last_name ?? '')))
                                    <option value="{{ $manager->id }}">{{ $managerName !== '' ? $managerName : '-' }} ({{ $manager->employee_code }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label>Reason</label>
                            <input type="text" name="reason" class="form-control" placeholder="Rejoin reason" required>
                        </div>

                        <div class="col-md-12">
                            <label>Comments</label>
                            <textarea name="comments" class="form-control" rows="3" placeholder="Optional notes"></textarea>
                        </div>

                        <div class="col-md-12 mt-2">
                            <button type="submit" class="btn btn-custom"><i class="icon-check"></i> Confirm Rejoin</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    if ($.fn.datepicker) {
        $('.status-date-picker').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true,
            todayHighlight: true
        });
    }
})();
</script>
@endpush
