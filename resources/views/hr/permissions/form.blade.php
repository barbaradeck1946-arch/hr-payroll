@extends('layouts.backend')

@section('content')
<div class="wrapper-page">
    <div class="page-title">
        <h1><i class="icon-key"></i> {{ $mode === 'edit' ? 'Edit Permission' : 'Add Permission' }}</h1>
    </div>

    @include('partials.flash')

    <div class="page-content">
        <div class="container-fluid">
            <div class="card no-border">
                <div class="content_wrapper" style="padding:20px;">
                    <form method="POST" action="{{ $mode === 'edit' ? route('permissions.update', $permission) : route('permissions.store') }}">
                        @csrf
                        @if($mode === 'edit')
                            @method('PUT')
                        @endif

                        <div class="form-group mb-3">
                            <label>Group Name</label>
                            <input type="text" name="group_name" class="form-control" value="{{ old('group_name', $permission->group_name ?? '') }}" required>
                        </div>

                        <div class="form-group mb-3">
                            <label>Name</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $permission->name ?? '') }}" required>
                        </div>

                        <div class="form-group mb-3">
                            <label>Slug (optional)</label>
                            <input type="text" name="slug" class="form-control" value="{{ old('slug', $permission->slug ?? '') }}">
                        </div>

                        <div class="form-group mb-3">
                            <label>Description</label>
                            <input type="text" name="description" class="form-control" value="{{ old('description', $permission->description ?? '') }}">
                        </div>

                        <div class="row">
                            <div class="col-md-3 form-group mb-3">
                                <label>Access Scope Key</label>
                                <input type="text" name="access_scope" class="form-control" value="{{ old('access_scope', $permission->access_scope ?? 'general') }}" required>
                            </div>
                            <div class="col-md-3 form-group mb-3">
                                <label>Access Scope Label</label>
                                <input type="text" name="access_scope_label" class="form-control" value="{{ old('access_scope_label', $permission->access_scope_label ?? 'General') }}" required>
                            </div>
                            <div class="col-md-3 form-group mb-3">
                                <label>Badge Class</label>
                                <input type="text" name="access_scope_badge_class" class="form-control" value="{{ old('access_scope_badge_class', $permission->access_scope_badge_class ?? 'bg-secondary') }}" required>
                            </div>
                            <div class="col-md-3 form-group mb-3">
                                <label>Scope Description</label>
                                <input type="text" name="access_scope_description" class="form-control" value="{{ old('access_scope_description', $permission->access_scope_description ?? '') }}">
                            </div>
                        </div>

                        <button class="btn btn-custom" type="submit">
                            <i class="{{ $mode === 'edit' ? 'icon-check' : 'icon-plus' }}"></i>
                            {{ $mode === 'edit' ? 'Update Permission' : 'Create Permission' }}
                        </button>
                        <a href="{{ route('permissions.index') }}" class="btn btn-custom-default"><i class="icon-arrow-left"></i> Back</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
