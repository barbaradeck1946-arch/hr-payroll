@extends('layouts.backend')

@section('content')
<div class="wrapper-page">
    <div class="page-title">
        <h1><i class="icon-user"></i> {{ $mode === 'edit' ? 'Edit Employee' : 'Add Employee' }}</h1>
    </div>

    @include('partials.flash')

    <div class="page-content">
        <div class="container-fluid">
            <div class="card no-border">
                <div class="content_wrapper" style="padding:20px;">
                    <form method="POST" action="{{ $mode === 'edit' ? route('employees.update', $employee) : route('employees.store') }}" enctype="multipart/form-data">
                        @csrf
                        @if($mode === 'edit') @method('PUT') @endif

                        <div class="row">
                            <div class="col-md-12 form-group mb-4">
                                <label>Profile Picture</label>
                                <div class="employee-avatar-card">
                                    <div class="employee-avatar-preview" id="employee_avatar_preview">
                                        @if(!empty($employee->avatar_path ?? null))
                                            <img src="{{ asset($employee->avatar_path) }}" alt="Employee Avatar">
                                        @else
                                            <i class="icon-user employee-avatar-icon"></i>
                                        @endif
                                    </div>
                                    <div class="employee-avatar-actions">
                                        <input type="file" name="avatar" id="user_image" accept=".jpg,.jpeg,.png,.webp">
                                        <label for="user_image" class="btn btn-custom btn-sm mb-2">
                                            <i class="icon-picture"></i> Upload Photo
                                        </label>
                                        <small id="avatar_file_name" class="text-muted d-block">No file chosen</small>
                                        <small class="text-muted d-block mt-1">JPG, PNG, WEBP. Max 2MB.</small>
                                        @if(!empty($employee->avatar_path ?? null))
                                            <label class="employee-avatar-remove mt-2">
                                                <input type="checkbox" name="remove_avatar" value="1">
                                                <span>Remove current photo</span>
                                            </label>
                                        @endif
                                    </div>
                                </div>
                            </div>

                                    <div class="col-md-4 form-group mb-3">
                                        <label>Employee Code (auto if blank)</label>
                                        <input type="text" name="employee_code" class="form-control" value="{{ old('employee_code', $employee->employee_code ?? '') }}" placeholder="Leave blank for auto generated code">
                                    </div>
                                    <div class="col-md-4 form-group mb-3">
                                        <label>First Name</label>
                                        <input type="text" name="first_name" class="form-control" value="{{ old('first_name', $employee->first_name ?? '') }}" placeholder="Enter first name" required>
                                    </div>
                                    <div class="col-md-4 form-group mb-3">
                                        <label>Last Name</label>
                                        <input type="text" name="last_name" class="form-control" value="{{ old('last_name', $employee->last_name ?? '') }}" placeholder="Enter last name">
                                    </div>

                            <div class="col-md-4 form-group mb-3">
                                <label>User Account (optional)</label>
                                <select name="user_id" class="form-control">
                                    <option value="">No linked user</option>
                                    @php($selectedUser = old('user_id', $employee->user_id ?? null))
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ (string)$selectedUser === (string)$user->id ? 'selected' : '' }}>{{ $user->name }} ({{ $user->email }})</option>
                                    @endforeach
                                </select>
                            </div>
                                <div class="col-md-4 form-group mb-3">
                                    <label>Gender</label>
                                    <select name="gender" class="form-control">
                                        <option value="">Select Gender</option>
                                        @foreach(['male','female','other'] as $gender)
                                            <option value="{{ $gender }}" {{ old('gender', $employee->gender ?? '') === $gender ? 'selected' : '' }}>{{ ucfirst($gender) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 form-group mb-3">
                                    <label>Date of Birth</label>
                                    <input type="text" name="date_of_birth" class="form-control datetimepicker" value="{{ old('date_of_birth', $employee->date_of_birth ?? '') }}" placeholder="YYYY-MM-DD">
                                </div>

                            <div class="col-md-4 form-group mb-3">
                                <label>Phone</label>
                                <input type="text" name="phone" class="form-control" value="{{ old('phone', $employee->phone ?? '') }}" placeholder="Enter phone number">
                            </div>
                            <div class="col-md-4 form-group mb-3">
                                <label>Alternate Phone</label>
                                <input type="text" name="alternate_phone" class="form-control" value="{{ old('alternate_phone', $employee->alternate_phone ?? '') }}" placeholder="Enter alternate phone number">
                            </div>
                                <div class="col-md-4 form-group mb-3">
                                    <label>Work Email</label>
                                    <input type="email" name="work_email" class="form-control" value="{{ old('work_email', $employee->work_email ?? '') }}" placeholder="Enter work email">
                                </div>

                            <div class="col-md-4 form-group mb-3">
                                <label>Employment Type</label>
                                @php($selectedType = old('employment_type', $employee->employment_type ?? 'full_time'))
                                <select name="employment_type" class="form-control" required>
                                    @foreach(['full_time','part_time','contract','intern'] as $type)
                                        <option value="{{ $type }}" {{ $selectedType === $type ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ', $type)) }}</option>
                                    @endforeach
                                </select>
                            </div>
                                <div class="col-md-4 form-group mb-3">
                                    <label>Employment Status</label>
                                    @php($selectedStatus = old('employment_status', $employee->employment_status ?? 'active'))
                                    <select name="employment_status" class="form-control" required>
                                        @foreach(['active','inactive','on_leave','on_notice','resigned','terminated'] as $status)
                                            <option value="{{ $status }}" {{ $selectedStatus === $status ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ', $status)) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                    <div class="col-md-4 form-group mb-3">
                        <label>Date of Joining</label>
                        <input type="text" name="date_of_joining" class="form-control datetimepicker" value="{{ old('date_of_joining', $employee->date_of_joining ?? '') }}" placeholder="YYYY-MM-DD" required>
                    </div>

                            <div class="col-md-4 form-group mb-3">
                                <label>Department</label>
                                <select name="department_id" class="form-control">
                                    <option value="">Select Department</option>
                                    @php($selectedDepartment = old('department_id', $employee->department_id ?? null))
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}" {{ (string)$selectedDepartment === (string)$department->id ? 'selected' : '' }}>{{ $department->name }}</option>
                                    @endforeach
                                </select>
                            </div>


                            <div class="col-md-4 form-group mb-3">
                                <label>Designation</label>
                                <select name="designation_id" class="form-control">
                                    <option value="">Select Designation</option>
                                    @php($selectedDesignation = old('designation_id', $employee->designation_id ?? null))
                                    @foreach($designations as $designation)
                                        <option value="{{ $designation->id }}" {{ (string)$selectedDesignation === (string)$designation->id ? 'selected' : '' }}>{{ $designation->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 form-group mb-3">
                                <label>Salary Grade</label>
                                <select name="salary_grade_id" class="form-control">
                                    <option value="">Select Grade</option>
                                    @php($selectedGrade = old('salary_grade_id', $employee->salary_grade_id ?? null))
                                    @foreach($salaryGrades as $grade)
                                        <option value="{{ $grade->id }}" {{ (string)$selectedGrade === (string)$grade->id ? 'selected' : '' }}>{{ $grade->grade_name }} ({{ $grade->grade_code }})</option>
                                    @endforeach
                                </select>
                            </div>

                                    <div class="col-md-6 form-group mb-3">
                                        <label>Reports To</label>
                                        <select name="reports_to_id" class="form-control">
                                            <option value="">No Manager</option>
                                            @php($selectedManager = old('reports_to_id', $employee->reports_to_id ?? null))
                                            @foreach($managers as $manager)
                                                <option value="{{ $manager->id }}" {{ (string)$selectedManager === (string)$manager->id ? 'selected' : '' }}>{{ trim($manager->first_name.' '.$manager->last_name) }} ({{ $manager->employee_code }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                            <div class="col-md-6 form-group mb-3">
                                <label>Marital Status</label>
                                <select name="marital_status" class="form-control">
                                    <option value="">Select</option>
                                    @foreach(['single','married','divorced','widowed'] as $marital)
                                        <option value="{{ $marital }}" {{ old('marital_status', $employee->marital_status ?? '') === $marital ? 'selected' : '' }}>{{ ucfirst($marital) }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4 form-group mb-3">
                                <label>NID Number</label>
                                <input type="text" name="nid_number" class="form-control" value="{{ old('nid_number', $employee->nid_number ?? '') }}" placeholder="Enter NID number">
                            </div>
                            <div class="col-md-4 form-group mb-3">
                                <label>Passport Number</label>
                                <input type="text" name="passport_number" class="form-control" value="{{ old('passport_number', $employee->passport_number ?? '') }}" placeholder="Enter passport number">
                            </div>
                            <div class="col-md-4 form-group mb-3">
                                <label>Tax ID</label>
                                <input type="text" name="tax_id" class="form-control" value="{{ old('tax_id', $employee->tax_id ?? '') }}" placeholder="Enter tax ID">
                            </div>

                            <div class="col-md-12 form-group mb-3">
                                <label>Notes</label>
                                <textarea name="notes" class="form-control" rows="3" placeholder="Write additional notes">{{ old('notes', $employee->notes ?? '') }}</textarea>
                            </div>
                        </div>

                        <button class="btn btn-custom" type="submit">
                            <i class="{{ $mode === 'edit' ? 'icon-check' : 'icon-plus' }}"></i>
                            {{ $mode === 'edit' ? 'Update Employee' : 'Create Employee' }}
                        </button>
                        <a href="{{ route('employees.index') }}" class="btn btn-custom-default"><i class="icon-arrow-left"></i> Back</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .employee-avatar-card {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 14px;
        border: 1px solid #e7ecf1;
        background: #fbfdff;
    }

    .employee-avatar-preview {
        width: 96px;
        height: 96px;
        border: 1px solid #d8e0ea;
        background: #fff;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .employee-avatar-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .employee-avatar-icon {
        font-size: 32px;
        color: #97a6b5;
    }

    .employee-avatar-actions .btn {
        min-width: 140px;
    }

    .employee-avatar-remove {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-weight: 500;
        color: #445b6e;
    }
</style>
@endpush

@push('scripts')
<script>
    (function () {
        var input = document.getElementById('user_image');
        var nameEl = document.getElementById('avatar_file_name');
        var preview = document.getElementById('employee_avatar_preview');
        if (!input || !nameEl || !preview) {
            return;
        }

        input.addEventListener('change', function () {
            if (!input.files || input.files.length === 0) {
                nameEl.textContent = 'No file chosen';
                return;
            }

            var file = input.files[0];
            nameEl.textContent = file.name;

            var reader = new FileReader();
            reader.onload = function (e) {
                preview.innerHTML = '<img src="' + e.target.result + '" alt="Employee Avatar">';
            };
            reader.readAsDataURL(file);

            var removeCheckbox = document.querySelector('input[name="remove_avatar"]');
            if (removeCheckbox) {
                removeCheckbox.checked = false;
            }
        });
    })();
</script>
@endpush
