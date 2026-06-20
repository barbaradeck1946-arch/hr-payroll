@extends('layouts.backend')

@section('content')
<div class="wrapper-page">
    <div class="page-title d-flex justify-content-between align-items-center">
        <h1><i class="icon-wallet"></i> Salary Templates</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('payroll.salary-template-assignments.create') }}" class="btn btn-custom-default"><i class="icon-user-follow"></i> Assign Template</a>
            <a href="{{ route('payroll.salary-templates.create') }}" class="btn btn-custom"><i class="icon-plus"></i> Add Template</a>
        </div>
    </div>

    @include('partials.flash')

    <div class="page-content">
        <div class="container-fluid">
            <div class="card no-border">
                <div class="content_wrapper" style="padding:20px;">
                    <form method="GET" class="row g-2 mb-3">
                        <div class="col-md-5"><input type="text" name="q" value="{{ $filters['q'] }}" class="form-control" placeholder="Search template name or code"></div>
                        <div class="col-md-2"><select name="per_page" class="form-control">@foreach([10,20,50,100] as $size)<option value="{{ $size }}" {{ (int)$filters['per_page']===$size?'selected':'' }}>{{ $size }} / page</option>@endforeach</select></div>
                        <div class="col-md-5 d-flex gap-2">
                            <button class="btn btn-custom" type="submit"><i class="icon-magnifier"></i> Filter</button>
                            <a href="{{ route('payroll.salary-templates.index') }}" class="btn btn-custom-default"><i class="icon-refresh"></i> Reset</a>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Code</th>
                                    <th>Frequency</th>
                                    <th>Basic</th>
                                    <th>Allowances</th>
                                    <th>PF %</th>
                                    <th>Tax %</th>
                                    <th>Employees</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($templates as $template)
                                    @php($allowances = (float)$template->house_rent + (float)$template->medical_allowance + (float)$template->conveyance_allowance + (float)$template->other_allowance)
                                    <tr>
                                        <td>{{ $template->name }}</td>
                                        <td>{{ $template->code }}</td>
                                        <td>{{ ucfirst($template->pay_frequency) }}</td>
                                        <td>{{ number_format((float) $template->basic_salary, 2) }}</td>
                                        <td>{{ number_format($allowances, 2) }}</td>
                                        <td>{{ number_format((float) $template->provident_fund_percent, 2) }}</td>
                                        <td>{{ number_format((float) $template->tax_percent, 2) }}</td>
                                        <td>{{ $template->employees_count }}</td>
                                        <td><span class="badge {{ $template->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $template->is_active ? 'Active' : 'Inactive' }}</span></td>
                                        <td class="action-buttons"><a href="{{ route('payroll.salary-templates.edit', $template) }}" title="Edit"><i class="icon-pencil"></i></a></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="10" class="text-center">No salary templates found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{ $templates->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
