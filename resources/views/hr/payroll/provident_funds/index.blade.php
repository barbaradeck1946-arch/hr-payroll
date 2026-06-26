@extends('layouts.backend')

@section('content')
<div class="wrapper-page">
    <div class="page-title"><h1><i class="icon-shield"></i> Provident Fund Setup</h1></div>
    @include('partials.flash')
    <div class="page-content">
        <div class="container-fluid">
            <div class="card no-border">
                <div class="content_wrapper content-padded">
                    @if($canManageProvidentFund ?? false)
                        <form method="POST" action="{{ route('payroll.provident-funds.store') }}" class="row g-2 mb-4">
                            @csrf
                            <div class="col-md-3"><select name="employee_id" class="form-control js-example-basic-single" required><option value="">Employee</option>@foreach($employees as $employee)<option value="{{ $employee->id }}">{{ trim($employee->first_name.' '.$employee->last_name) }} ({{ $employee->employee_code }})</option>@endforeach</select></div>
                            <div class="col-md-2"><input type="number" step="0.01" min="0" max="100" name="employee_contribution_percent" class="form-control" placeholder="Employee %" required></div>
                            <div class="col-md-2"><input type="number" step="0.01" min="0" max="100" name="employer_contribution_percent" class="form-control" placeholder="Employer %" required></div>
                            <div class="col-md-2"><input type="number" step="0.01" min="0" name="opening_balance" class="form-control" value="0" placeholder="Opening balance"></div>
                            <div class="col-md-2"><input type="text" name="effective_from" class="form-control datetimepicker" value="{{ now()->toDateString() }}" placeholder="Effective from"></div>
                            <div class="col-md-1"><button class="btn btn-custom w-100" type="submit"><i class="icon-check"></i></button></div>
                        </form>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead><tr><th>Employee</th><th>Employee %</th><th>Employer %</th><th>Opening Balance</th><th>Effective From</th></tr></thead>
                            <tbody>
                                @forelse($funds as $fund)
                                    <tr>
                                        <td>{{ trim($fund->employee?->first_name.' '.$fund->employee?->last_name) }} <small class="text-muted">({{ $fund->employee?->employee_code }})</small></td>
                                        <td>{{ number_format((float)$fund->employee_contribution_percent, 2) }}</td>
                                        <td>{{ number_format((float)$fund->employer_contribution_percent, 2) }}</td>
                                        <td>{{ number_format((float)$fund->opening_balance, 2) }}</td>
                                        <td>{{ $fund->effective_from ?: '-' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center">No provident fund setup found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $funds->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
