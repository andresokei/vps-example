@if (session()->has('impersonator_id'))
  <div class="impersonation-banner">
    <div>
      <strong>{{ __('Read-only view as') }} {{ auth()->user()->name }}</strong>
      <span class="d-none d-md-inline">({{ auth()->user()->email }})</span>
    </div>
    <form method="POST" action="{{ route('admin.impersonation.stop') }}">
      @csrf
      <button type="submit" class="btn btn-sm btn-light">
        <i class="bi bi-arrow-left-circle me-1"></i>{{ __('Back to admin') }}
      </button>
    </form>
  </div>
@endif
