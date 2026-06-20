@extends('layouts.backend')

@section('content')
<div class="wrapper-page">
    <div class="page-title d-flex justify-content-between align-items-center">
        <h1><i class="icon-docs"></i> Payroll Run</h1>
        <div class="d-flex gap-2">
            @if($run->status === 'draft')
                <form method="POST" action="{{ route('payroll.runs.finalize', $run) }}" onsubmit="return confirm('Finalize this payroll run? After final submission, salary calculations will be locked for this run.');">
                    @csrf
                    <button type="submit" class="btn btn-custom"><i class="icon-check"></i> Final Submit</button>
                </form>
            @endif
            <a href="{{ route('payroll.runs.index') }}" class="btn btn-custom-default"><i class="icon-arrow-left"></i> Back</a>
        </div>
    </div>
    @include('partials.flash')
    <div class="page-content">
        <div class="container-fluid">
            <div class="card no-border">
                <div class="content_wrapper" style="padding:20px;">
                    <div class="row g-2 mb-3">
                        <div class="col-md-3"><strong>Period:</strong> {{ $run->period_label ?: '-' }}</div>
                        <div class="col-md-3"><strong>Range:</strong> {{ $run->period_start }} to {{ $run->period_end }}</div>
                        <div class="col-md-2"><strong>Gross:</strong> {{ number_format((float)$run->gross_total, 2) }}</div>
                        <div class="col-md-2"><strong>Deductions:</strong> {{ number_format((float)$run->deduction_total, 2) }}</div>
                        <div class="col-md-2"><strong>Net:</strong> {{ number_format((float)$run->net_total, 2) }}</div>
                        <div class="col-md-3"><strong>Status:</strong> <span class="badge bg-secondary">{{ ucfirst($run->status) }}</span></div>
                        <div class="col-md-3"><strong>Finalized By:</strong> {{ $run->processor?->name ?: '-' }}</div>
                    </div>

                    @if($run->status === 'draft')
                        <div class="alert alert-info">
                            Review all payroll items below. Final submission will mark this run as processed and post provident fund transactions.
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead><tr><th>Employee</th><th>Department</th><th>Basic</th><th>Allowances</th><th>Bonus</th><th>Loan</th><th>Other Ded.</th><th>PF</th><th>Tax</th><th>Net</th><th>Status</th><th>Actions</th></tr></thead>
                            <tbody>
                                @forelse($run->items as $item)
                                    <tr>
                                        <td>{{ trim($item->employee?->first_name.' '.$item->employee?->last_name) }} <small class="text-muted">({{ $item->employee?->employee_code }})</small></td>
                                        <td>{{ $item->employee?->department?->name ?: '-' }}</td>
                                        <td>{{ number_format((float)$item->basic_salary, 2) }}</td>
                                        <td>{{ number_format((float)$item->allowance_total, 2) }}</td>
                                        <td>{{ number_format((float)$item->bonus_total, 2) }}</td>
                                        <td>{{ number_format((float)$item->loan_deduction, 2) }}</td>
                                        <td>{{ number_format((float)$item->other_deduction, 2) }}</td>
                                        <td>{{ number_format((float)$item->provident_fund_deduction, 2) }}</td>
                                        <td>{{ number_format((float)$item->tax_deduction, 2) }}</td>
                                        <td>{{ number_format((float)$item->net_payable, 2) }}</td>
                                        <td><span class="badge bg-secondary">{{ ucfirst($item->payment_status) }}</span></td>
                                        <td class="action-buttons"><a href="{{ route('payroll.items.show', $item) }}" title="Payslip"><i class="icon-doc"></i></a></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="12" class="text-center">No payroll items found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
