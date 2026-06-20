@extends('layouts.backend')

@section('content')
<div class="wrapper-page">
    <div class="page-title"><h1><i class="icon-present"></i> Bonuses</h1></div>
    @include('partials.flash')
    <div class="page-content">
        <div class="container-fluid">
            <div class="card no-border">
                <div class="content_wrapper" style="padding:20px;">
                    <form method="POST" action="{{ route('payroll.bonuses.store') }}" class="row g-2 mb-4">
                        @csrf
                        <div class="col-md-3"><select name="employee_id" class="form-control js-example-basic-single" required><option value="">Employee</option>@foreach($employees as $employee)<option value="{{ $employee->id }}">{{ trim($employee->first_name.' '.$employee->last_name) }} ({{ $employee->employee_code }})</option>@endforeach</select></div>
                        <div class="col-md-2"><input type="text" name="title" class="form-control" placeholder="Title" required></div>
                        <div class="col-md-2"><input type="number" step="0.01" min="0" name="amount" class="form-control" placeholder="Amount" required></div>
                        <div class="col-md-2"><input type="text" name="bonus_date" class="form-control datetimepicker" value="{{ now()->toDateString() }}" placeholder="Bonus date" required></div>
                        <div class="col-md-2"><input type="text" name="bonus_type" class="form-control" value="performance" placeholder="Type" required></div>
                        <div class="col-md-1"><button class="btn btn-custom w-100" type="submit"><i class="icon-plus"></i></button></div>
                        <div class="col-md-12"><textarea name="remarks" class="form-control" rows="2" placeholder="Remarks"></textarea></div>
                    </form>

                    <form method="GET" class="row g-2 mb-3">
                        <div class="col-md-4"><select name="employee_id" class="form-control"><option value="0">All Employees</option>@foreach($employees as $employee)<option value="{{ $employee->id }}" {{ (int)$filters['employee_id']===$employee->id?'selected':'' }}>{{ trim($employee->first_name.' '.$employee->last_name) }} ({{ $employee->employee_code }})</option>@endforeach</select></div>
                        <div class="col-md-2"><select name="per_page" class="form-control">@foreach([10,20,50,100] as $size)<option value="{{ $size }}" {{ (int)$filters['per_page']===$size?'selected':'' }}>{{ $size }} / page</option>@endforeach</select></div>
                        <div class="col-md-6 d-flex gap-2"><button class="btn btn-custom" type="submit"><i class="icon-magnifier"></i> Filter</button><a href="{{ route('payroll.bonuses.index') }}" class="btn btn-custom-default"><i class="icon-refresh"></i> Reset</a></div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead><tr><th>Employee</th><th>Title</th><th>Type</th><th>Date</th><th>Amount</th><th>Created By</th><th>Actions</th></tr></thead>
                            <tbody>
                                @forelse($bonuses as $bonus)
                                    <tr>
                                        <td>{{ trim($bonus->employee?->first_name.' '.$bonus->employee?->last_name) }} <small class="text-muted">({{ $bonus->employee?->employee_code }})</small></td>
                                        <td>{{ $bonus->title }}</td>
                                        <td>{{ ucfirst($bonus->bonus_type) }}</td>
                                        <td>{{ $bonus->bonus_date }}</td>
                                        <td>{{ number_format((float)$bonus->amount, 2) }}</td>
                                        <td>{{ $bonus->creator?->name ?: '-' }}</td>
                                        <td class="action-buttons"><form method="POST" action="{{ route('payroll.bonuses.destroy', $bonus) }}" onsubmit="return confirm('Delete this bonus?');">@csrf @method('DELETE')<button type="submit"><i class="icon-trash"></i></button></form></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="text-center">No bonuses found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $bonuses->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
