@extends('layouts.app')
@section('tabTitle', __("view.edit"))
@section('content')
<div class="wrapper">
    <div class="page-wrapper">
        <div class="container-xl">
            <!-- Page title -->
            <div class="page-header d-print-none">
                <div class="row align-items-center">
                    <div class="col">
                        <h2 class="page-title">
                            {{ __("view.edit") }} {{ $admin->name }}
                        </h2>
                    </div>
                </div>
            </div>
        </div>
        <div class="page-body">
            <form action="{{ route('admin.update') }}" method="POST" data-form class="card">
                @csrf
                <input type="hidden" name="id" value="{{ $admin->id }}">
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="row">
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label class="form-label">{{ __("view.name") }}</label>
                                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ $admin->name }}">
                                        @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="mb-3">
                                        <div class="form-label">{{ __('view.email') }}</div>
                                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ $admin->email }}">
                                        @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <div class="form-label">{{ __('view.role') }}</div>
                                        <div>
                                            <label class="form-check form-check-inline">
                                                <input class="form-check-input @error('role') is-invalid @enderror" name="role" type="radio" value="0" @if($admin->role == 0) checked @endif>
                                                <span class="form-check-label">Admin</span>
                                            </label>
                                            <label class="form-check form-check-inline">
                                                <input class="form-check-input @error('role') is-invalid @enderror" name="role" type="radio" value="1" @if($admin->role == 1) checked @endif>
                                                <span class="form-check-label">Super Admin</span>
                                            </label>
                                            @error('role')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="card-footer text-end">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <button type="submit" class="btn btn-primary ms-auto">{{ __('view.submit') }}</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    $('[admins_tab]').addClass('active');
</script>
@endsection