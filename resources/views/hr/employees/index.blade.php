@extends('layouts.backend')

@section('content')
<div class="wrapper-page">
    @php
        $authUser = auth()->user();
        $canCreateEmployee = $authUser?->hasPermission('employee.create') ?? false;
        $canUpdateEmployee = $authUser?->hasPermission('employee.update') ?? false;
        $canDeleteEmployee = $authUser?->hasPermission('employee.delete') ?? false;
        $canViewProfile = ($authUser?->hasPermission('employee.view') ?? false) || ($authUser?->hasPermission('employee.view-profile') ?? false);
    @endphp
    <div class="page-title d-flex justify-content-between align-items-center">
        <h1><i class="icon-user"></i> Employees</h1>
        @if($canCreateEmployee)
            <a href="{{ route('employees.create') }}" class="btn btn-custom"><i class="icon-plus"></i> Add Employee</a>
        @endif
    </div>

    @include('partials.flash')

    <div class="page-content">
        <div class="container-fluid">
            <div class="card no-border">
                <div class="content_wrapper" style="padding:20px;">
                    <form method="GET" class="row g-2 mb-3">
                        <div class="col-md-3">
                            <input type="text" name="q" value="{{ $filters['q'] }}" class="form-control" placeholder="Search code/name/phone/email">
                        </div>
                        <div class="col-md-2">
                            <select name="department_id" class="form-control">
                                <option value="0">All Departments</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}" {{ (int) $filters['department_id'] === $department->id ? 'selected' : '' }}>{{ $department->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="designation_id" class="form-control">
                                <option value="0">All Designations</option>
                                @foreach($designations as $designation)
                                    <option value="{{ $designation->id }}" {{ (int) $filters['designation_id'] === $designation->id ? 'selected' : '' }}>{{ $designation->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="employment_status" class="form-control">
                                <option value="">All Status</option>
                                @foreach(['active','inactive','on_leave','terminated'] as $status)
                                    <option value="{{ $status }}" {{ $filters['employment_status'] === $status ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ', $status)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="employment_type" class="form-control">
                                <option value="">All Types</option>
                                @foreach(['full_time','part_time','contract','intern'] as $type)
                                    <option value="{{ $type }}" {{ $filters['employment_type'] === $type ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ', $type)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-1">
                            <select name="per_page" class="form-control">
                                @foreach([10,20,50,100] as $size)
                                    <option value="{{ $size }}" {{ (int) $filters['per_page'] === $size ? 'selected' : '' }}>{{ $size }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12 d-flex gap-2 mt-2">
                            <button class="btn btn-custom" type="submit"><i class="icon-magnifier"></i> Filter</button>
                            <a href="{{ route('employees.index') }}" class="btn btn-custom-default"><i class="icon-refresh"></i> Reset</a>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>Department</th>
                                    <th>Designation</th>
                                    <th>Manager</th>
                                    <th>Status</th>
                                    <th>Type</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($employees as $employee)
                                    <tr>
                                        <td>{{ $employee->employee_code }}</td>
                                        <td>
                                            {{ trim($employee->first_name.' '.$employee->last_name) }}
                                            @if($employee->work_email)
                                                <div class="small text-muted">{{ $employee->work_email }}</div>
                                            @endif
                                        </td>
                                        <td>{{ $employee->department?->name ?? '-' }}</td>
                                        <td>{{ $employee->designation?->name ?? '-' }}</td>
                                        <td>{{ $employee->manager ? trim($employee->manager->first_name.' '.$employee->manager->last_name) : '-' }}</td>
                                        <td>{{ ucfirst(str_replace('_', ' ', $employee->employment_status)) }}</td>
                                        <td>{{ ucfirst(str_replace('_', ' ', $employee->employment_type)) }}</td>
                                        <td class="action-buttons">
                                            @if($canViewProfile)
                                                <a href="{{ route('employees.show', $employee) }}" title="View Profile"><i class="icon-eye"></i></a>
                                            @endif
                                            @if($canUpdateEmployee)
                                                <a href="{{ route('employees.edit', $employee) }}" title="Edit Employee"><i class="icon-pencil"></i></a>
                                            @endif
                                            @if($canDeleteEmployee)
                                                <form method="POST" action="{{ route('employees.destroy', $employee) }}" style="display:inline;" onsubmit="return confirm('Delete this employee?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" title="Delete Employee"><i class="icon-trash"></i></button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="8" class="text-center">No employees found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{ $employees->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
