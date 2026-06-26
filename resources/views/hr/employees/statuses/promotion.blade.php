@extends('layouts.backend')
@section('title', 'Employee Promotion')

@section('content')
<div class="wrapper-page">
    <div class="page-title d-flex justify-content-between align-items-center">
        <h1><i class="icon-arrow-up-circle"></i> Employee Promotion</h1>
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
                        <div class="col-md-4"><strong>Current Department:</strong> {{ $employee->department?->name ?? '-' }}</div>
                        <div class="col-md-4"><strong>Current Designation:</strong> {{ $employee->designation?->name ?? '-' }}</div>
                        <div class="col-md-4"><strong>Current Salary Grade:</strong> {{ $employee->salaryGrade?->grade_name ?? '-' }}</div>
                    </div>

                    <form method="POST" action="{{ route('employee-statuses.promote', $employee) }}" class="row g-2">
                        @csrf

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
                            <label>Effective Date</label>
                            <input type="text" name="effective_date" class="form-control status-date-picker" value="{{ now()->toDateString() }}" placeholder="YYYY-MM-DD" required>
                        </div>

                        <div class="col-md-4">
                            <label>Revised Salary (optional)</label>
                            <input type="number" step="0.01" min="0" name="revised_salary" class="form-control" placeholder="e.g. 45000">
                        </div>

                        <div class="col-md-4">
                            <label>Reason</label>
                            <input type="text" name="reason" class="form-control" placeholder="Promotion reason" required>
                        </div>

                        <div class="col-md-12">
                            <label>Comments</label>
                            <textarea name="comments" class="form-control" rows="3" placeholder="Optional notes"></textarea>
                        </div>

                        <div class="col-md-12 mt-2">
                            <button type="submit" class="btn btn-custom"><i class="icon-check"></i> Submit Promotion</button>
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
