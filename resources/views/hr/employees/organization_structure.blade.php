@extends('layouts.backend')

@section('content')
<div class="wrapper-page">
    <div class="page-title d-flex justify-content-between align-items-center">
        <h1><i class="icon-share-alt"></i> Organization Structure</h1>
    </div>

    @include('partials.flash')

    <div class="page-content">
        <div class="container-fluid">
            @if($authEmployee)
                <div class="card no-border mb-3">
                    <div class="content_wrapper content-padded">
                        <h5 class="mb-3">My Reporting Line</h5>
                        @if($supervisorChain->isEmpty())
                            <div class="alert alert-info mb-2">No supervisor assigned for your profile.</div>
                        @else
                            <ol class="mb-0">
                                @foreach($supervisorChain as $supervisor)
                                    <li>
                                        {{ trim($supervisor->first_name.' '.$supervisor->last_name) }}
                                        ({{ $supervisor->employee_code }})
                                        @if($supervisor->designation?->name)
                                            - {{ $supervisor->designation->name }}
                                        @endif
                                    </li>
                                @endforeach
                            </ol>
                        @endif

                        <hr>
                        <h5 class="mb-3">My Direct Subordinates</h5>
                        @if($mySubordinates->isEmpty())
                            <div class="alert alert-info mb-0">No direct subordinates found.</div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-bordered mb-0">
                                    <thead>
                                        <tr>
                                            <th>Code</th>
                                            <th>Name</th>
                                            <th>Department</th>
                                            <th>Designation</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($mySubordinates as $item)
                                            <tr>
                                                <td>{{ $item->employee_code }}</td>
                                                <td>{{ trim($item->first_name.' '.$item->last_name) }}</td>
                                                <td>{{ $item->department?->name ?? '-' }}</td>
                                                <td>{{ $item->designation?->name ?? '-' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <div class="card no-border">
                <div class="content_wrapper content-padded">
                    <h5 class="mb-3">Company Employee Structure</h5>
                    @php($grouped = $employees->groupBy(fn ($item) => $item->department?->name ?? 'Unassigned Department'))

                    @forelse($grouped as $departmentName => $items)
                        <h6 class="mt-3">{{ $departmentName }}</h6>
                        <div class="table-responsive mb-3">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>Employee</th>
                                        <th>Designation</th>
                                        <th>Reports To</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($items as $employee)
                                        <tr>
                                            <td>{{ $employee->employee_code }}</td>
                                            <td>{{ trim($employee->first_name.' '.$employee->last_name) }}</td>
                                            <td>{{ $employee->designation?->name ?? '-' }}</td>
                                            <td>
                                                @if($employee->manager)
                                                    {{ trim($employee->manager->first_name.' '.$employee->manager->last_name) }} ({{ $employee->manager->employee_code }})
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>{{ ucfirst(str_replace('_', ' ', $employee->employment_status)) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @empty
                        <div class="alert alert-info mb-0">No employee data found.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
