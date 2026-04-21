@extends('layouts.backend')

@section('content')
<div class="wrapper-page">
    <div class="page-title">
        <h1><i class="icon-lock"></i> Change Password</h1>
    </div>

    @include('partials.flash')

    <div class="page-content">
        <div class="container-fluid">
            <div class="card no-border">
                <div class="content_wrapper" style="padding:20px; max-width: 720px;">
                    <form method="POST" action="{{ route('dashboard.password.update') }}">
                        @csrf
                        @method('PUT')

                        <div class="form-group mb-3">
                            <label>Old Password</label>
                            <input type="password" name="current_password" class="form-control" required autocomplete="current-password">
                        </div>

                        <div class="form-group mb-3">
                            <label>New Password</label>
                            <input type="password" name="password" class="form-control" required autocomplete="new-password">
                        </div>

                        <div class="form-group mb-4">
                            <label>Confirm New Password</label>
                            <input type="password" name="password_confirmation" class="form-control" required autocomplete="new-password">
                        </div>

                        <button type="submit" class="btn btn-custom">
                            <i class="icon-check"></i> Update Password
                        </button>
                        <a href="{{ route('dashboard') }}" class="btn btn-custom-default"><i class="icon-arrow-left"></i> Back</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
