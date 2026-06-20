@extends('layouts.backend')

@section('content')
<div class="wrapper-page">
    <div class="page-title d-flex justify-content-between align-items-center">
        <h1><i class="icon-credit-card"></i> Loan Detail</h1>
        <a href="{{ route('payroll.loans.index') }}" class="btn btn-custom-default"><i class="icon-arrow-left"></i> Back</a>
    </div>

    @include('partials.flash')

    @php($paidTotal = (float) $loan->installments->sum('paid_amount'))
    @php($remainingTotal = max(0, (float) $loan->principal_amount - $paidTotal))
    @php($hasPaidInstallments = $loan->installments->contains(fn($installment) => $installment->status === 'paid'))

    <div class="page-content">
        <div class="container-fluid">
            <div class="card no-border">
                <div class="content_wrapper" style="padding:20px;">
                    <div class="row g-3 mb-3">
                        <div class="col-md-3"><strong>Employee:</strong><br>{{ trim($loan->employee?->first_name.' '.$loan->employee?->last_name) }} ({{ $loan->employee?->employee_code }})</div>
                        <div class="col-md-3"><strong>Department:</strong><br>{{ $loan->employee?->department?->name ?: '-' }}</div>
                        <div class="col-md-2"><strong>Reference:</strong><br>{{ $loan->loan_reference }}</div>
                        <div class="col-md-2"><strong>Status:</strong><br><span class="badge bg-secondary">{{ ucfirst($loan->status) }}</span></div>
                        <div class="col-md-2"><strong>Issued:</strong><br>{{ $loan->issued_date }}</div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-3"><strong>Principal:</strong><br>{{ number_format((float) $loan->principal_amount, 2) }}</div>
                        <div class="col-md-3"><strong>Paid:</strong><br>{{ number_format($paidTotal, 2) }}</div>
                        <div class="col-md-3"><strong>Remaining:</strong><br>{{ number_format($remainingTotal, 2) }}</div>
                        <div class="col-md-3"><strong>Installment:</strong><br>{{ $loan->installment_count }} x {{ number_format((float) $loan->installment_amount, 2) }}</div>
                    </div>

                    <h5 class="table_banner_title mb-2">Update Loan Status</h5>
                    <form method="POST" action="{{ route('payroll.loans.status', $loan) }}" class="row g-2 mb-4">
                        @csrf
                        @method('PATCH')
                        <div class="col-md-3">
                            <select name="status" class="form-control" required>
                                @foreach(['active', 'paused', 'closed'] as $status)
                                    <option value="{{ $status }}" {{ $loan->status === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6"><input type="text" name="remarks" class="form-control" value="{{ $loan->remarks }}" placeholder="Remarks"></div>
                        <div class="col-md-3"><button class="btn btn-custom" type="submit"><i class="icon-check"></i> Update Status</button></div>
                    </form>

                    @if(! $hasPaidInstallments)
                        <h5 class="table_banner_title mb-2">Reschedule Loan</h5>
                        <form method="POST" action="{{ route('payroll.loans.reschedule', $loan) }}" class="row g-2 mb-4">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="employee_id" value="{{ $loan->employee_id }}">
                            <div class="col-md-2"><input type="text" name="loan_reference" class="form-control" value="{{ old('loan_reference', $loan->loan_reference) }}" required></div>
                            <div class="col-md-2"><input type="number" step="0.01" min="0" name="principal_amount" class="form-control" value="{{ old('principal_amount', $loan->principal_amount) }}" required></div>
                            <div class="col-md-1"><input type="number" step="0.01" min="0" max="100" name="interest_rate_percent" class="form-control" value="{{ old('interest_rate_percent', $loan->interest_rate_percent) }}"></div>
                            <div class="col-md-1"><input type="number" min="1" name="installment_count" class="form-control" value="{{ old('installment_count', $loan->installment_count) }}" required></div>
                            <div class="col-md-2"><input type="number" step="0.01" min="0" name="installment_amount" class="form-control" value="{{ old('installment_amount', $loan->installment_amount) }}" required></div>
                            <div class="col-md-2"><input type="text" name="issued_date" class="form-control datetimepicker" value="{{ old('issued_date', $loan->issued_date) }}" required></div>
                            <div class="col-md-2"><input type="text" name="first_installment_date" class="form-control datetimepicker" value="{{ old('first_installment_date', $loan->first_installment_date) }}"></div>
                            <div class="col-md-2"><select name="status" class="form-control">@foreach(['active','paused','closed'] as $status)<option value="{{ $status }}" {{ old('status', $loan->status)===$status?'selected':'' }}>{{ ucfirst($status) }}</option>@endforeach</select></div>
                            <div class="col-md-7"><input type="text" name="remarks" class="form-control" value="{{ old('remarks', $loan->remarks) }}" placeholder="Remarks"></div>
                            <div class="col-md-3"><button class="btn btn-custom" type="submit"><i class="icon-refresh"></i> Reschedule</button></div>
                        </form>
                    @else
                        <div class="alert alert-info">This loan has paid installments. Rescheduling is locked to protect payroll history.</div>
                    @endif

                    <h5 class="table_banner_title mb-2">Installment Schedule</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Due Date</th>
                                    <th>Amount</th>
                                    <th>Paid Amount</th>
                                    <th>Paid Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($loan->installments as $installment)
                                    <tr>
                                        <td>{{ $installment->installment_no }}</td>
                                        <td>{{ $installment->due_date }}</td>
                                        <td>{{ number_format((float) $installment->amount, 2) }}</td>
                                        <td>{{ number_format((float) $installment->paid_amount, 2) }}</td>
                                        <td>{{ $installment->paid_date ?: '-' }}</td>
                                        <td><span class="badge bg-secondary">{{ ucfirst($installment->status) }}</span></td>
                                        <td>
                                            @if($installment->status !== 'paid')
                                                <form method="POST" action="{{ route('payroll.loan-installments.paid', $installment) }}" class="d-flex gap-2">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="text" name="paid_date" class="form-control datetimepicker" value="{{ now()->toDateString() }}" style="max-width: 150px;">
                                                    <button class="btn btn-custom btn-sm" type="submit"><i class="icon-check"></i> Paid</button>
                                                </form>
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="text-center">No installments found.</td></tr>
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
