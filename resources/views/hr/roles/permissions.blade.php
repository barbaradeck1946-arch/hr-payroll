@extends('layouts.backend')

@section('content')
<div class="wrapper-page">
    <div class="page-title">
        <h1><i class="icon-shield"></i> Role Permissions: {{ $role->name }}</h1>
    </div>

    @include('partials.flash')

    <div class="page-content">
        <div class="container-fluid">
            <div class="card no-border">
                <div class="content_wrapper" style="padding:20px;">
                    <div class="row g-2 mb-3">
                        @foreach($permissionScopeLegend as $scope)
                            <div class="col-md-3">
                                <div class="p-2 h-100" style="border:1px solid #e5e7eb; border-radius:8px;">
                                    <span class="badge {{ $scope->access_scope_badge_class }}">{{ $scope->access_scope_label }}</span>
                                    <div class="text-muted mt-2" style="font-size:12px; line-height:1.4;">{{ $scope->access_scope_description }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <form method="POST" action="{{ route('roles.permissions.sync', $role) }}">
                        @csrf

                        @foreach($permissionsByGroup as $group => $permissions)
                            <div class="mb-3 p-2" style="border:1px solid #e5e7eb; border-radius:8px;">
                                <h6 class="mb-2 text-uppercase">{{ str_replace('_', ' ', $group) }}</h6>
                                <div class="row">
                                    @foreach($permissions as $permission)
                                        <div class="col-md-3 mb-2">
                                            @php($checkboxId = 'permission_'.$role->id.'_'.$permission->id)
                                            @php($accessScope = $permission->accessScopeMeta())
                                            <div class="checkbox checkbox-default">
                                                <input id="{{ $checkboxId }}" type="checkbox" name="permission_ids[]" value="{{ $permission->id }}" {{ in_array($permission->id, $selectedPermissionIds, true) ? 'checked' : '' }}>
                                                <label for="{{ $checkboxId }}">
                                                    {{ $permission->name }}
                                                    <span class="badge {{ $accessScope['badge_class'] }} ms-1" title="{{ $accessScope['description'] }}">{{ $accessScope['label'] }}</span>
                                                </label>
                                            </div>
                                            <div class="text-muted" style="font-size:11px; margin-left:24px;">{{ $permission->slug }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach

                        <button class="btn btn-custom" type="submit"><i class="icon-check"></i> Save Permissions</button>
                        <a href="{{ route('roles.index') }}" class="btn btn-custom-default"><i class="icon-arrow-left"></i> Back</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
