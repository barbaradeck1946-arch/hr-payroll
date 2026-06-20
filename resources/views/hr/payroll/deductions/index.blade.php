@extends('layouts.backend')

@section('content')
<div class="wrapper-page">
    <div class="page-title"><h1><i class="icon-minus"></i> Employee Deductions</h1></div>
    @include('partials.flash')
    <div class="page-content">
        <div class="container-fluid">
            <div class="card no-border">
                <div class="content_wrapper" style="padding:20px;">
                    <form method="POST" action="{{ route('payroll.deductions.store') }}" class="row g-2 mb-4">
                        @csrf
                        <div class="col-md-3"><select name="employee_id" class="form-control js-example-basic-single" required><option value="">Employee</option>@foreach($employees as $employee)<option value="{{ $employee->id }}">{{ trim($employee->first_name.' '.$employee->last_name) }} ({{ $employee->employee_code }})</option>@endforeach</select></div>
                        <div class="col-md-2"><input type="text" name="deduction_type" class="form-control" placeholder="Type" required></div>
                        <div class="col-md-2"><select name="calculation_type" class="form-control"><option value="fixed">Fixed</option><option value="percent">Percent</option></select></div>
                        <div class="col-md-2"><input type="number" step="0.01" min="0" name="amount" class="form-control" placeholder="Amount" required></div>
                        <div class="col-md-2"><select name="frequency" class="form-control"><option value="monthly">Monthly</option><option value="weekly">Weekly</option><option value="one_time">One Time</option></select></div>
                        <div class="col-md-1"><button class="btn btn-custom w-100" type="submit"><i class="icon-plus"></i></button></div>
                        <div class="col-md-2"><input type="text" name="effective_from" class="form-control datetimepicker" value="{{ now()->toDateString() }}" placeholder="Effective from" required></div>
                        <div class="col-md-2"><input type="text" name="effective_to" class="form-control datetimepicker" placeholder="Effective to"></div>
                        <div class="col-md-2"><select name="is_active" class="form-control"><option value="1">Active</option><option value="0">Inactive</option></select></div>
                        <div class="col-md-3"><input type="text" name="reason" class="form-control" placeholder="Reason"></div>
                        <div class="col-md-3"><input type="text" name="comments" class="form-control" placeholder="Comments"></div>
                    </form>

                    <form method="GET" class="row g-2 mb-3">
                        <div class="col-md-3"><select name="employee_id" class="form-control"><option value="0">All Employees</option>@foreach($employees as $employee)<option value="{{ $employee->id }}" {{ (int)$filters['employee_id']===$employee->id?'selected':'' }}>{{ trim($employee->first_name.' '.$employee->last_name) }} ({{ $employee->employee_code }})</option>@endforeach</select></div>
                        <div class="col-md-2"><select name="status" class="form-control"><option value="">All Status</option><option value="active" {{ $filters['status']==='active'?'selected':'' }}>Active</option><option value="inactive" {{ $filters['status']==='inactive'?'selected':'' }}>Inactive</option></select></div>
                        <div class="col-md-2"><select name="per_page" class="form-control">@foreach([10,20,50,100] as $size)<option value="{{ $size }}" {{ (int)$filters['per_page']===$size?'selected':'' }}>{{ $size }} / page</option>@endforeach</select></div>
                        <div class="col-md-5 d-flex gap-2"><button class="btn btn-custom" type="submit"><i class="icon-magnifier"></i> Filter</button><a href="{{ route('payroll.deductions.index') }}" class="btn btn-custom-default"><i class="icon-refresh"></i> Reset</a></div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead><tr><th>Employee</th><th>Type</th><th>Calculation</th><th>Amount</th><th>Frequency</th><th>Effective</th><th>Status</th><th>Actions</th></tr></thead>
                            <tbody>
                                @forelse($deductions as $deduction)
                                    <tr>
                                        <td>{{ trim($deduction->employee?->first_name.' '.$deduction->employee?->last_name) }} <small class="text-muted">({{ $deduction->employee?->employee_code }})</small></td>
                                        <td>{{ $deduction->deduction_type }}</td>
                                        <td>{{ ucfirst($deduction->calculation_type) }}</td>
                                        <td>{{ number_format((float)$deduction->amount, 2) }}</td>
                                        <td>{{ ucfirst(str_replace('_', ' ', $deduction->frequency)) }}</td>
                                        <td>{{ $deduction->effective_from }} - {{ $deduction->effective_to ?: 'Open' }}</td>
                                        <td><span class="badge {{ $deduction->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $deduction->is_active ? 'Active' : 'Inactive' }}</span></td>
                                        <td class="action-buttons"><form method="POST" action="{{ route('payroll.deductions.destroy', $deduction) }}" onsubmit="return confirm('Delete this deduction?');">@csrf @method('DELETE')<button type="submit"><i class="icon-trash"></i></button></form></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="8" class="text-center">No deductions found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $deductions->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
