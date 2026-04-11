@extends('layouts.dashboard')

@section('title', __('My Profile'))

@section('content')
<div class="page-header">
  <h1 class="page-title">{{ __('My Profile') }}</h1>
  <p class="page-subtitle">{{ __('Manage your account information.') }}</p>
</div>

@if (session('status') === 'profile-updated')
  <div class="alert alert-success d-flex align-items-center gap-2 mb-4">
    <i class="bi bi-check-circle-fill flex-shrink-0"></i>
    {{ __('Profile updated successfully.') }}
  </div>
@endif

@if (session('status') === 'password-updated')
  <div class="alert alert-success d-flex align-items-center gap-2 mb-4">
    <i class="bi bi-check-circle-fill flex-shrink-0"></i>
    {{ __('Password updated successfully.') }}
  </div>
@endif

<div class="row g-4">

  {{-- Profile information --}}
  <div class="col-lg-6">
    <div class="card h-100">
      <div class="card-header">
        <h5 class="card-title">
          <i class="bi bi-person me-2 text-primary"></i>{{ __('Personal Information') }}
        </h5>
      </div>
      <div class="card-body">
        <form method="POST" action="{{ route('profile.update') }}">
          @csrf
          @method('PATCH')

          <div class="mb-3">
            <label for="name" class="form-label">{{ __('Full name') }}</label>
            <input type="text"
                   id="name"
                   name="name"
                   class="form-control @error('name') is-invalid @enderror"
                   value="{{ old('name', $user->name) }}"
                   required
                   autocomplete="name">
            @error('name')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="mb-4">
            <label for="email" class="form-label">{{ __('Email address') }}</label>
            <input type="email"
                   id="email"
                   name="email"
                   class="form-control @error('email') is-invalid @enderror"
                   value="{{ old('email', $user->email) }}"
                   required
                   autocomplete="username">
            @error('email')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <button type="submit" class="btn btn-primary">
            <i class="bi bi-floppy me-1"></i> {{ __('Save changes') }}
          </button>
        </form>
      </div>
    </div>
  </div>

  {{-- Change password --}}
  <div class="col-lg-6">
    <div class="card h-100">
      <div class="card-header">
        <h5 class="card-title">
          <i class="bi bi-lock me-2 text-primary"></i>{{ __('Change Password') }}
        </h5>
      </div>
      <div class="card-body">
        <form method="POST" action="{{ route('password.update') }}">
          @csrf
          @method('PUT')

          <div class="mb-3">
            <label for="current_password" class="form-label">{{ __('Current password') }}</label>
            <input type="password"
                   id="current_password"
                   name="current_password"
                   class="form-control @error('current_password', 'updatePassword') is-invalid @enderror"
                   autocomplete="current-password">
            @error('current_password', 'updatePassword')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="mb-3">
            <label for="password" class="form-label">{{ __('New password') }}</label>
            <input type="password"
                   id="password"
                   name="password"
                   class="form-control @error('password', 'updatePassword') is-invalid @enderror"
                   autocomplete="new-password">
            @error('password', 'updatePassword')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="mb-4">
            <label for="password_confirmation" class="form-label">{{ __('Confirm new password') }}</label>
            <input type="password"
                   id="password_confirmation"
                   name="password_confirmation"
                   class="form-control"
                   autocomplete="new-password">
          </div>

          <button type="submit" class="btn btn-primary">
            <i class="bi bi-shield-lock me-1"></i> {{ __('Update password') }}
          </button>
        </form>
      </div>
    </div>
  </div>

  {{-- Delete account --}}
  <div class="col-12">
    <div class="card border-danger-subtle">
      <div class="card-header" style="border-bottom-color:#fee2e2;">
        <h5 class="card-title text-danger">
          <i class="bi bi-exclamation-triangle me-2"></i>{{ __('Danger Zone') }}
        </h5>
      </div>
      <div class="card-body">
        <p class="text-muted mb-3" style="font-size:0.875rem;">
          {{ __('Once your account is deleted, all data will be permanently removed. This action cannot be undone.') }}
        </p>
        <button type="button"
                class="btn btn-outline-danger btn-sm"
                data-bs-toggle="modal"
                data-bs-target="#deleteAccountModal">
          <i class="bi bi-trash me-1"></i> {{ __('Delete account') }}
        </button>
      </div>
    </div>
  </div>

</div>

{{-- Delete account modal --}}
<div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-danger" style="border-bottom-color:#fee2e2 !important;">
        <h5 class="modal-title text-danger">
          <i class="bi bi-exclamation-triangle me-2"></i>{{ __('Confirm deletion') }}
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
      </div>
      <div class="modal-body">
        <p class="mb-3" style="font-size:0.875rem;">
          {{ __('To confirm deletion, enter your current password.') }}
        </p>
        <form id="deleteAccountForm" method="POST" action="{{ route('profile.destroy') }}">
          @csrf
          @method('DELETE')
          <div class="mb-3">
            <label for="delete_password" class="form-label">{{ __('Password') }}</label>
            <input type="password"
                   id="delete_password"
                   name="password"
                   class="form-control @error('password', 'userDeletion') is-invalid @enderror"
                   placeholder="{{ __('Enter your password') }}"
                   required>
            @error('password', 'userDeletion')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
        <button type="submit" form="deleteAccountForm" class="btn btn-danger">
          <i class="bi bi-trash me-1"></i> {{ __('Delete account permanently') }}
        </button>
      </div>
    </div>
  </div>
</div>

@endsection
