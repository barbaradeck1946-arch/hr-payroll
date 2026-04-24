@extends('layouts.backend')

@section('content')
<div class="wrapper-page">

    <div class="page-title">
        <h1><i class="icon-grid"></i>
            Dashboard
        </h1>
    </div>
    @include('partials.flash')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-3">
                    <div class="card border-0">
                        <div class="card-body bg-dutch widget">
                            <div class="d-flex">
                                <div class="align-items-center">
                                    <h4 class="text-white">
                                        
                                    </h4>
                                    <h6 class="text-white">
                                        Today's Sale
                                    </h6>
                                </div>
                                <div class="ms-auto align-items-center">
                                    <span class="text-white widget-icon"><i class="icon-layers"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0">
                        <div class="card-body bg-jade widget">
                            <div class="d-flex">
                                <div class="align-items-center">
                                    <h4 class="text-white">
                                        
                                    </h4>
                                    <h6 class="text-white">
                                        
                                    </h6>
                                </div>
                                <div class="ms-auto align-items-center">
                                    <span class="text-white widget-icon"><i class="icon-doc"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0">
                        <div class="card-body bg-green widget">
                            <div class="d-flex">
                                <div class="align-items-center">
                                    <h4 class="text-white">
                                        
                                    </h4>
                                    <h6 class="text-white">
                                        
                                    </h6>
                                </div>
                                <div class="ms-auto align-items-center">
                                    <span class="text-white widget-icon"><i class="icon-wallet"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0">
                        <div class="card-body bg-blue widget">
                            <div class="d-flex">
                                <div class="align-items-center">
                                    <h4 class="text-white">
                                        
                                    </h4>
                                    <h6 class="text-white">
                                        Total Products
                                    </h6>
                                </div>
                                <div class="ms-auto align-items-center">
                                    <span class="text-white widget-icon"><i class="icon-bag"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-8">
                    <div class="card no-border">
                        <div class="content_wrapper">
                            <div class="table_banner clearfix">
                                <h5 class="table_banner_title">
                                    Sales Progress | Latest Notices & Announcements
                                </h5>
                            <div class="float-end d-flex gap-2">
                                <a href="{{ route('announcements.index') }}" class="btn btn-custom-default btn-sm">View All</a>
                                @if($canCreateAnnouncement ?? false)
                                    <a href="{{ route('announcements.create') }}" class="btn btn-custom btn-sm">Add New</a>
                                @endif
                            </div>
                            </div>
                            <div class="dashboard-notice-meta px-3 pt-2 pb-0">
                                <span class="dashboard-notice-chip"><i class="icon-bell"></i> Notices: {{ ($latestAnnouncements ?? collect())->count() }}</span>
                                <span class="dashboard-notice-chip"><i class="icon-clock"></i> Non-expired only</span>
                            </div>
                            <div class="table_body dash-table-widget" style="padding: 20px;">
                                <div class="table-responsive">
                                    <table class="table table-bordered dashboard-notice-table" style="margin-bottom: 0;">
                                        <thead>
                                            <tr>
                                                <th><i class="icon-doc me-1"></i> Title</th>
                                                <th><i class="icon-tag me-1"></i> Type</th>
                                                <th><i class="icon-clock me-1"></i> Published At</th>
                                                <th><i class="icon-eye me-1"></i> Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse(($latestAnnouncements ?? collect()) as $item)
                                                <tr>
                                                    <td>
                                                    <strong>{{ $item->title }}</strong>
                                                    </td>
                                                    <td>
                                                    <span class="badge {{ $item->announcement_type === 'notice' ? 'bg-info' : 'bg-primary' }}">
                                                        <i class="{{ $item->announcement_type === 'notice' ? 'icon-bell' : 'icon-doc' }}"></i>
                                                        {{ ucfirst($item->announcement_type) }}
                                                    </span>
                                                    </td>
                                                    <td>{{ $item->publish_at?->format('Y-m-d H:i') ?? '-' }}</td>
                                                    <td class="action-buttons">
                                                        <a href="{{ route('announcements.show', $item) }}" title="Details"><i class="icon-eye"></i></a>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="text-center">No active notices/announcements available.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 form-group">
                    <div class="card no-border no-bgc clearfix">
                        <div class="table_banner clearfix">
                            <h5 class="table_banner_title">
                                Quick Notes
                            </h5>
                            <h5 class="table_banner_title float-end"><i class="icon-notebook"></i></h5>
                        </div>
                        <div class="bg-white">
                            <div class="slimScrollNote">
                                <div class="todo-box-wrap">
                                    <ul class="todo-list">
                                        
                                        <li class="todo-item">
                                            
                                            <div class="checkbox checkbox-default">
                                                <input class="to-do" data-id="" data-value="0" type="checkbox" id="">
                                                <label for=""></label>
                                            </div>
                                            
                                            <div class="checkbox checkbox-default">
                                                <input class="to-do" data-id="" data-value="1" type="checkbox" id="" checked>
                                                <label for=""></label>
                                            </div>
                                            
                                        </li>
                                        <li>
                                            <hr class="light-grey-hr">
                                        </li>
                                        
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="new-todo">
                            <form method="post" enctype="multipart/form-data" id="add_todo">
                                <div class="input-group">

                                    <input type="text" id="todo_data" name="todo_data" class="form-control" style="border: 1px solid #fff !IMPORTANT; width: 100% !IMPORTANT;" placeholder="Add New Tasks">
                                    <span class="input-group-btn">

                                        <input type="hidden" name="userid" id="userid" value="">

                                        <button type="submit" class="btn btn-success todo-submit"><i class="fa fa-plus"></i></button>
                                    </span>

                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <!--Top 10 product-->
                <div class="col-md-6">
                    <div class="card no-border">
                        <div class="content_wrapper">
                            <div class="table_banner clearfix">
                                <h5 class="table_banner_title">
                                    Top 10 Product
                                </h5>
                                <h5 class="table_banner_title float-end"><i class="icon-handbag"></i></h5>
                            </div>
                            <style>
                                .table tr td {
                                    padding: 15px 10px !IMPORTANT;
                                }
                            </style>
                            <div class="table_body no-padding dash-table-widget" style="padding: 20px; margin-bottom: 0;">
                                <table class="table" style="margin-bottom: 0;">
                                    <!-- start head -->
                                    <thead>
                                        <tr>
                                            <th>
                                                Image
                                            </th>
                                            <th>
                                                Title
                                            </th>
                                            <th>
                                                Sold
                                            </th>
                                        </tr>
                                    </thead>
                                    <!-- end head -->
                                    <!-- start body -->
                                    <tbody>
                                        <!-- start rows -->
                                        
                                        <tr>
                                            <td><img alt="madpos" src="../../../public/assets/img/product/"> </td>
                                            <td>
                                                
                                            </td>
                                            <td>
                                                
                                            </td>
                                        </tr>
                                        
                                        <!-- end rows -->
                                    </tbody>
                                    <!-- end body -->
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <!--Top 10 product end-->
                <!--Top 10 Customer start-->
                <div class="col-md-6">
                    <div class="card no-border">
                        <div class="content_wrapper">
                            <div class="table_banner clearfix">
                                <h5 class="table_banner_title">
                                    Top 10 Customer
                                </h5>
                                <h5 class="table_banner_title float-end"><i class="icon-people"></i></h5>
                            </div>
                            <style>
                                .table tr td {
                                    padding: 15px 10px !IMPORTANT;
                                }
                            </style>
                            <div class="table_body no-padding dash-table-widget" style="padding: 20px; margin-bottom: 0;">
                                <table class="table" style="margin-bottom: 0;">
                                    <!-- start head -->
                                    <thead>
                                        <tr>
                                            <th>
                                                Image
                                            </th>
                                            <th>
                                                Full Name
                                            </th>
                                            <th>
                                                Total
                                            </th>
                                        </tr>
                                    </thead>
                                    <!-- end head -->
                                    <!-- start body -->
                                    <tbody>
                                        <!-- start rows -->
                                        
                                        <tr>
                                            <td>
                                                
                                                <img alt="madpos" src="../../../public/assets/img/customer/">
                                                
                                                <img alt="madpos" src="../../../public/assets/img/user/default.jpg">
                                                
                                            </td>
                                            <td>
                                                
                                            </td>
                                            <td>
                                                
                                            </td>
                                        </tr>
                                        
                                        <!-- end rows -->
                                    </tbody>
                                    <!-- end body -->
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <!--Top 10 Customer end-->
                <!--Todays expense start-->
                <div class="col-md-6">
                    <div class="card no-border">
                        <div class="content_wrapper">
                            <div class="table_banner clearfix">
                                <h5 class="table_banner_title">
                                    Today's Expense
                                </h5>
                                <h5 class="table_banner_title float-end"><i class="icon-calculator"></i></h5>
                            </div>
                            <style>
                                .table tr td {
                                    padding: 15px 10px !IMPORTANT;
                                }
                            </style>
                            <div class="table_body dash-table-widget no-padding" style="padding: 20px; margin-bottom: 0;">
                                <table class="table" style="margin-bottom: 0;">
                                    <!-- start head -->
                                    <thead>
                                        <tr>
                                            <th>
                                                Expense Name
                                            </th>
                                            <th>
                                                Expense By
                                            </th>
                                            <th>
                                                Amount
                                            </th>
                                        </tr>
                                    </thead>
                                    <!-- end head -->
                                    <!-- start body -->
                                    <tbody>
                                        <!-- start rows -->
                                        
                                        <tr>
                                            <td class="text-primary">
                                                
                                            </td>
                                            <td>
                                                
                                            </td>
                                            <td>
                                                
                                            </td>
                                        </tr>
                                        
                                        <!-- end rows -->
                                    </tbody>
                                    <!-- end body -->
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <!--Todays expense end-->
            </div>
        </div>
    </div>
    <!-- /.page-content  -->
</div>
@endsection

@push('styles')
<style>
    .dashboard-notice-meta {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }
    .dashboard-notice-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        color: #4e5d6c;
        border: 1px solid #e6e9ee;
        border-radius: 6px;
        padding: 4px 9px;
    }
    .dashboard-notice-table thead th {
        white-space: nowrap;
        border-bottom-width: 2px;
    }
    .dashboard-notice-table .badge i {
        margin-right: 4px;
    }
</style>
@endpush
