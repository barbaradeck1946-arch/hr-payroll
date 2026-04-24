@extends('layouts.backend')
@section('title', $mode === 'edit' ? 'Edit Leave Category' : 'Add Leave Category')

@section('content')
<div class="wrapper-page">
    <div class="page-title">
        <h1><i class="icon-layers"></i> {{ $mode === 'edit' ? 'Edit Leave Category' : 'Add Leave Category' }}</h1>
    </div>

    @include('partials.flash')

    <div class="page-content">
        <div class="container-fluid">
            <div class="card no-border">
                <div class="content_wrapper" style="padding:20px;">
                    <form method="POST" action="{{ $mode === 'edit' ? route('leave-categories.update', $leaveCategory) : route('leave-categories.store') }}">
                        @csrf
                        @if($mode === 'edit')
                            @method('PUT')
                        @endif

                        <div class="row">
                            <div class="col-md-6 form-group mb-3">
                                <label>Name</label>
                                <input type="text" name="name" class="form-control" value="{{ old('name', $leaveCategory->name ?? '') }}" maxlength="120" required>
                            </div>

                            <div class="col-md-6 form-group mb-3">
                                <label>Code</label>
                                <input type="text" name="code" class="form-control" value="{{ old('code', $leaveCategory->code ?? '') }}" maxlength="30" required>
                            </div>

                            <div class="col-md-4 form-group mb-3">
                                <label>Paid Leave</label>
                                @php($isPaid = (int) old('is_paid', isset($leaveCategory) ? (int) $leaveCategory->is_paid : 1))
                                <select name="is_paid" class="form-control" required>
                                    <option value="1" {{ $isPaid === 1 ? 'selected' : '' }}>Yes</option>
                                    <option value="0" {{ $isPaid === 0 ? 'selected' : '' }}>No</option>
                                </select>
                            </div>

                            <div class="col-md-4 form-group mb-3">
                                <label>Attachment Required</label>
                                @php($requiresAttachment = (int) old('requires_attachment', isset($leaveCategory) ? (int) $leaveCategory->requires_attachment : 0))
                                <select name="requires_attachment" class="form-control" required>
                                    <option value="0" {{ $requiresAttachment === 0 ? 'selected' : '' }}>No</option>
                                    <option value="1" {{ $requiresAttachment === 1 ? 'selected' : '' }}>Yes</option>
                                </select>
                            </div>

                            <div class="col-md-4 form-group mb-3">
                                <label>Max Consecutive Days</label>
                                <input type="number" min="1" max="255" name="max_consecutive_days" class="form-control" value="{{ old('max_consecutive_days', $leaveCategory->max_consecutive_days ?? '') }}">
                            </div>

                            <div class="col-md-6 form-group mb-3">
                                <label>Status</label>
                                @php($isActive = (int) old('is_active', isset($leaveCategory) ? (int) $leaveCategory->is_active : 1))
                                <select name="is_active" class="form-control" required>
                                    <option value="1" {{ $isActive === 1 ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ $isActive === 0 ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>

                            <div class="col-md-12 form-group mb-3">
                                <label>Description</label>
                                <textarea name="description" class="form-control" rows="3">{{ old('description', $leaveCategory->description ?? '') }}</textarea>
                            </div>
                        </div>

                        <button class="btn btn-custom" type="submit">
                            <i class="{{ $mode === 'edit' ? 'icon-check' : 'icon-plus' }}"></i>
                            {{ $mode === 'edit' ? 'Update Category' : 'Create Category' }}
                        </button>
                        <a href="{{ route('leave-categories.index') }}" class="btn btn-custom-default"><i class="icon-arrow-left"></i> Back</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
