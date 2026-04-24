@extends('layouts.backend')
@section('title', $mode === 'edit' ? 'Edit Leave Policy' : 'Add Leave Policy')

@section('content')
<div class="wrapper-page">
    <div class="page-title">
        <h1><i class="icon-calendar"></i> {{ $mode === 'edit' ? 'Edit Leave Policy' : 'Add Leave Policy' }}</h1>
    </div>

    @include('partials.flash')

    <div class="page-content">
        <div class="container-fluid">
            <div class="card no-border">
                <div class="content_wrapper" style="padding:20px;">
                    <form method="POST" action="{{ $mode === 'edit' ? route('leave-policies.update', $leavePolicy) : route('leave-policies.store') }}">
                        @csrf
                        @if($mode === 'edit')
                            @method('PUT')
                        @endif

                        <div class="row">
                            <div class="col-md-6 form-group mb-3">
                                <label>Leave Category</label>
                                <select name="leave_category_id" class="form-control" required>
                                    <option value="">Select Leave Category</option>
                                    @foreach($leaveCategories as $leaveCategory)
                                        <option value="{{ $leaveCategory->id }}" {{ (int) old('leave_category_id', $leavePolicy->leave_category_id ?? 0) === (int) $leaveCategory->id ? 'selected' : '' }}>
                                            {{ $leaveCategory->name }} ({{ $leaveCategory->code }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 form-group mb-3">
                                <label>Salary Grade</label>
                                <select name="salary_grade_id" class="form-control" required>
                                    <option value="">Select Salary Grade</option>
                                    @foreach($salaryGrades as $salaryGrade)
                                        <option value="{{ $salaryGrade->id }}" {{ (int) old('salary_grade_id', $leavePolicy->salary_grade_id ?? 0) === (int) $salaryGrade->id ? 'selected' : '' }}>
                                            {{ $salaryGrade->grade_name }} ({{ $salaryGrade->grade_code }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3 form-group mb-3">
                                <label>Effective From Year</label>
                                <input type="number" min="2000" max="2100" name="effective_from_year" class="form-control" value="{{ old('effective_from_year', $leavePolicy->effective_from_year ?? now()->year) }}" required>
                            </div>

                            <div class="col-md-3 form-group mb-3">
                                <label>Effective To Year</label>
                                <input type="number" min="2000" max="2100" name="effective_to_year" class="form-control" value="{{ old('effective_to_year', $leavePolicy->effective_to_year ?? '') }}">
                            </div>

                            <div class="col-md-3 form-group mb-3">
                                <label>Days Allocated</label>
                                <input type="number" step="0.01" min="0" max="366" name="days_allocated" class="form-control" value="{{ old('days_allocated', $leavePolicy->days_allocated ?? '0') }}" required>
                            </div>

                            <div class="col-md-3 form-group mb-3">
                                <label>Prorated</label>
                                @php($isProrated = (int) old('is_prorated', isset($leavePolicy) ? (int) $leavePolicy->is_prorated : 1))
                                <select name="is_prorated" class="form-control" required>
                                    <option value="1" {{ $isProrated === 1 ? 'selected' : '' }}>Yes</option>
                                    <option value="0" {{ $isProrated === 0 ? 'selected' : '' }}>No</option>
                                </select>
                            </div>

                            <div class="col-md-4 form-group mb-3">
                                <label>Carry Forward Mode</label>
                                @php($carryMode = old('carry_forward_mode', $leavePolicy->carry_forward_mode ?? 'none'))
                                <select name="carry_forward_mode" id="carry_forward_mode" class="form-control" required>
                                    <option value="none" {{ $carryMode === 'none' ? 'selected' : '' }}>None</option>
                                    <option value="limited" {{ $carryMode === 'limited' ? 'selected' : '' }}>Limited</option>
                                    <option value="full" {{ $carryMode === 'full' ? 'selected' : '' }}>Full</option>
                                </select>
                            </div>

                            <div class="col-md-4 form-group mb-3" id="carry_forward_limit_group">
                                <label>Carry Forward Limit</label>
                                <input type="number" step="0.01" min="0" max="366" name="carry_forward_limit" class="form-control" value="{{ old('carry_forward_limit', $leavePolicy->carry_forward_limit ?? '') }}">
                            </div>

                            <div class="col-md-4 form-group mb-3">
                                <label>Status</label>
                                @php($isActive = (int) old('is_active', isset($leavePolicy) ? (int) $leavePolicy->is_active : 1))
                                <select name="is_active" class="form-control" required>
                                    <option value="1" {{ $isActive === 1 ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ $isActive === 0 ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>

                            <div class="col-md-4 form-group mb-3">
                                <label>Earned Leave</label>
                                @php($isEarnedLeave = (int) old('is_earned_leave', isset($leavePolicy) ? (int) $leavePolicy->is_earned_leave : 0))
                                <select name="is_earned_leave" id="is_earned_leave" class="form-control" required>
                                    <option value="0" {{ $isEarnedLeave === 0 ? 'selected' : '' }}>No</option>
                                    <option value="1" {{ $isEarnedLeave === 1 ? 'selected' : '' }}>Yes</option>
                                </select>
                            </div>

                            <div class="col-md-4 form-group mb-3 earned-leave-group">
                                <label>Earned Credit Frequency</label>
                                @php($earnedCreditFrequency = old('earned_credit_frequency', $leavePolicy->earned_credit_frequency ?? 'monthly'))
                                <select name="earned_credit_frequency" class="form-control">
                                    <option value="monthly" {{ $earnedCreditFrequency === 'monthly' ? 'selected' : '' }}>Monthly</option>
                                    <option value="yearly" {{ $earnedCreditFrequency === 'yearly' ? 'selected' : '' }}>Yearly</option>
                                </select>
                            </div>

                            <div class="col-md-4 form-group mb-3 earned-leave-group">
                                <label>Earned Credit Days</label>
                                <input type="number" step="0.01" min="0" max="31" name="earned_credit_days" class="form-control" value="{{ old('earned_credit_days', $leavePolicy->earned_credit_days ?? '0') }}">
                            </div>

                            <div class="col-md-12 form-group mb-3">
                                <label>Notes</label>
                                <textarea name="notes" class="form-control" rows="3">{{ old('notes', $leavePolicy->notes ?? '') }}</textarea>
                            </div>
                        </div>

                        <button class="btn btn-custom" type="submit">
                            <i class="{{ $mode === 'edit' ? 'icon-check' : 'icon-plus' }}"></i>
                            {{ $mode === 'edit' ? 'Update Policy' : 'Create Policy' }}
                        </button>
                        <a href="{{ route('leave-policies.index') }}" class="btn btn-custom-default"><i class="icon-arrow-left"></i> Back</a>
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
    var carryMode = document.getElementById('carry_forward_mode');
    var carryLimitGroup = document.getElementById('carry_forward_limit_group');
    var earnedLeaveSelect = document.getElementById('is_earned_leave');

    function toggleCarryLimit() {
        if (!carryMode || !carryLimitGroup) {
            return;
        }

        carryLimitGroup.style.display = carryMode.value === 'limited' ? '' : 'none';
    }

    function toggleEarnedLeave() {
        if (!earnedLeaveSelect) {
            return;
        }

        var groups = document.querySelectorAll('.earned-leave-group');
        groups.forEach(function (el) {
            el.style.display = earnedLeaveSelect.value === '1' ? '' : 'none';
        });
    }

    toggleCarryLimit();
    toggleEarnedLeave();

    if (carryMode) {
        carryMode.addEventListener('change', toggleCarryLimit);
    }

    if (earnedLeaveSelect) {
        earnedLeaveSelect.addEventListener('change', toggleEarnedLeave);
    }
})();
</script>
@endpush
