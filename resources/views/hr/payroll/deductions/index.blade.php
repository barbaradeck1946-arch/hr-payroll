@extends('layouts.backend')

@section('content')
<div class="wrapper-page">
    <div class="page-title"><h1><i class="icon-minus"></i> {{ __('Employee Deductions') }}</h1></div>
    @include('partials.flash')
    <div class="page-content">
        <div class="container-fluid">
            <div class="card no-border">
                <div class="content_wrapper content-padded">
                    @if($canManageDeductions ?? false)
                        <form method="POST" action="{{ route('payroll.deductions.store') }}" class="row g-2 mb-4">
                            @csrf
                            <div class="col-md-3"><select name="employee_id" class="form-control js-example-basic-single" required><option value="">{{ __('Employee') }}</option>@foreach($employees as $employee)<option value="{{ $employee->id }}">{{ trim($employee->first_name.' '.$employee->last_name) }} ({{ $employee->employee_code }})</option>@endforeach</select></div>
                            <div class="col-md-2"><input type="text" name="deduction_type" class="form-control" placeholder="{{ __('Type') }}" required></div>
                            <div class="col-md-2"><select name="calculation_type" class="form-control"><option value="fixed">{{ __('Fixed') }}</option><option value="percent">{{ __('Percent') }}</option></select></div>
                            <div class="col-md-2"><input type="number" step="0.01" min="0" name="amount" class="form-control" placeholder="{{ __('Amount') }}" required></div>
                            <div class="col-md-2"><select name="frequency" class="form-control"><option value="monthly">{{ __('Monthly') }}</option><option value="weekly">{{ __('Weekly') }}</option><option value="one_time">{{ __('One Time') }}</option></select></div>
                            <div class="col-md-1"><button class="btn btn-custom w-100" type="submit"><i class="icon-plus"></i></button></div>
                            <div class="col-md-2"><input type="text" name="effective_from" class="form-control datetimepicker" value="{{ now()->toDateString() }}" placeholder="{{ __('Effective from') }}" required></div>
                            <div class="col-md-2"><input type="text" name="effective_to" class="form-control datetimepicker" placeholder="{{ __('Effective to') }}"></div>
                            <div class="col-md-2"><select name="is_active" class="form-control"><option value="1">{{ __('Active') }}</option><option value="0">{{ __('Inactive') }}</option></select></div>
                            <div class="col-md-3"><input type="text" name="reason" class="form-control" placeholder="{{ __('Reason') }}"></div>
                            <div class="col-md-3"><input type="text" name="comments" class="form-control" placeholder="{{ __('Comments') }}"></div>
                        </form>
                    @endif

                    <form method="GET" class="row g-2 mb-3">
                        <div class="col-md-3">
                            @if($canViewAllDeductions ?? false)
                                <select name="employee_id" class="form-control">
                                    <option value="0">{{ __('All Employees') }}</option>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}" {{ (int)$filters['employee_id']===$employee->id?'selected':'' }}>{{ trim($employee->first_name.' '.$employee->last_name) }} ({{ $employee->employee_code }})</option>
                                    @endforeach
                                </select>
                            @else
                                <select name="employee_id" class="form-control" disabled>
                                    @forelse($employees as $employee)
                                        <option>{{ trim($employee->first_name.' '.$employee->last_name) }} ({{ $employee->employee_code }})</option>
                                    @empty
                                        <option>{{ __('My deduction records') }}</option>
                                    @endforelse
                                </select>
                            @endif
                        </div>
                        <div class="col-md-2"><select name="status" class="form-control"><option value="">{{ __('All Status') }}</option><option value="active" {{ $filters['status']==='active'?'selected':'' }}>{{ __('Active') }}</option><option value="inactive" {{ $filters['status']==='inactive'?'selected':'' }}>{{ __('Inactive') }}</option></select></div>
                        <div class="col-md-2"><select name="per_page" class="form-control">@foreach([10,20,50,100] as $size)<option value="{{ $size }}" {{ (int)$filters['per_page']===$size?'selected':'' }}>{{ $size }} / page</option>@endforeach</select></div>
                        <div class="col-md-5 d-flex gap-2"><button class="btn btn-custom" type="submit"><i class="icon-magnifier"></i> {{ __('Filter') }}</button><a href="{{ route('payroll.deductions.index') }}" class="btn btn-custom-default"><i class="icon-refresh"></i> {{ __('Reset') }}</a></div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead><tr><th>{{ __('Employee') }}</th><th>{{ __('Type') }}</th><th>{{ __('Calculation') }}</th><th>{{ __('Amount') }}</th><th>{{ __('Frequency') }}</th><th>{{ __('Effective') }}</th><th>{{ __('Status') }}</th><th>{{ __('Actions') }}</th></tr></thead>
                            <tbody>
                                @forelse($deductions as $deduction)
                                    <tr>
                                        <td>{{ trim($deduction->employee?->first_name.' '.$deduction->employee?->last_name) }} <small class="text-muted">({{ $deduction->employee?->employee_code }})</small></td>
                                        <td>{{ $deduction->deduction_type }}</td>
                                        <td>{{ __(ucfirst($deduction->calculation_type)) }}</td>
                                        <td>{{ number_format((float)$deduction->amount, 2) }}</td>
                                        <td>{{ __(ucfirst(str_replace('_', ' ', $deduction->frequency))) }}</td>
                                        <td>{{ $deduction->effective_from }} - {{ $deduction->effective_to ?: 'Open' }}</td>
                                        <td><span class="badge {{ $deduction->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $deduction->is_active ? __('Active') : __('Inactive') }}</span></td>
                                        <td class="action-buttons">
                                            @if($canManageDeductions ?? false)
                                                <form method="POST" action="{{ route('payroll.deductions.destroy', $deduction) }}" onsubmit="return confirm('Delete this deduction?');">@csrf @method('DELETE')<button type="submit"><i class="icon-trash"></i></button></form>
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="8" class="text-center">{{ __('No deductions found.') }}</td></tr>
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
