@extends('layouts.backend')

@section('content')
<div class="wrapper-page">
    <div class="page-title d-flex justify-content-between align-items-center">
        <h1><i class="icon-doc"></i> Payslip Detail</h1>
        <a href="{{ route('payroll.runs.show', $item->payrollRun) }}" class="btn btn-custom-default"><i class="icon-arrow-left"></i> Back</a>
    </div>
    @include('partials.flash')
    <div class="page-content">
        <div class="container-fluid">
            <div class="card no-border">
                <div class="content_wrapper content-padded">
                    <div class="row g-2 mb-3">
                        <div class="col-md-4"><strong>Employee:</strong> {{ trim($item->employee?->first_name.' '.$item->employee?->last_name) }} ({{ $item->employee?->employee_code }})</div>
                        <div class="col-md-4"><strong>Department:</strong> {{ $item->employee?->department?->name ?: '-' }}</div>
                        <div class="col-md-4"><strong>Period:</strong> {{ $item->payrollRun?->period_label ?: '-' }}</div>
                    </div>

                    <div class="table-responsive mb-3">
                        <table class="table table-bordered">
                            <tbody>
                                <tr><th>Basic Salary</th><td>{{ number_format((float)$item->basic_salary, 2) }}</td></tr>
                                <tr><th>Allowance Total</th><td>{{ number_format((float)$item->allowance_total, 2) }}</td></tr>
                                <tr><th>Bonus Total</th><td>{{ number_format((float)$item->bonus_total, 2) }}</td></tr>
                                <tr><th>Loan Deduction</th><td>{{ number_format((float)$item->loan_deduction, 2) }}</td></tr>
                                <tr><th>Other Deduction</th><td>{{ number_format((float)$item->other_deduction, 2) }}</td></tr>
                                <tr><th>Provident Fund Deduction</th><td>{{ number_format((float)$item->provident_fund_deduction, 2) }}</td></tr>
                                <tr><th>Tax Deduction</th><td>{{ number_format((float)$item->tax_deduction, 2) }}</td></tr>
                                <tr><th>Total Deduction</th><td>{{ number_format((float)$item->total_deduction, 2) }}</td></tr>
                                <tr><th>Net Payable</th><td><strong>{{ number_format((float)$item->net_payable, 2) }}</strong></td></tr>
                            </tbody>
                        </table>
                    </div>

                    <h5 class="table_banner_title mb-2">Deduction Breakdown</h5>
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered">
                            <thead><tr><th>Type</th><th>Amount</th><th>Reason</th><th>Comments</th></tr></thead>
                            <tbody>
                                @forelse($item->deductions as $deduction)
                                    <tr><td>{{ $deduction->deduction_type }}</td><td>{{ number_format((float)$deduction->amount, 2) }}</td><td>{{ $deduction->reason ?: '-' }}</td><td>{{ $deduction->comments ?: '-' }}</td></tr>
                                @empty
                                    <tr><td colspan="4" class="text-center">No deduction breakdown found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($item->payrollRun?->status === 'draft')
                        <div class="alert alert-info">
                            This payslip is part of a draft payroll run. Payment can be marked only after final submission.
                        </div>
                    @elseif($item->payment_status !== 'paid')
                        <form method="POST" action="{{ route('payroll.items.paid', $item) }}" class="row g-2">
                            @csrf
                            @method('PATCH')
                            <div class="col-md-4"><input type="text" name="payment_reference" class="form-control" placeholder="Payment reference"></div>
                            <div class="col-md-2"><button class="btn btn-custom" type="submit"><i class="icon-check"></i> Mark Paid</button></div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
