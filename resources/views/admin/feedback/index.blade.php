@extends('layouts.dashboard')

@section('title', 'Admin - Feedback')

@section('content')
<div class="page-header d-flex flex-column flex-md-row justify-content-between gap-3">
  <div>
    <h1 class="page-title">{{ __('User feedback') }}</h1>
    <p class="page-subtitle">{{ __('Read and track comments, problems and improvement ideas from users.') }}</p>
  </div>
</div>

@if (session('status'))
  <div class="alert alert-success">
    <i class="bi bi-check-circle me-1"></i>{{ session('status') }}
  </div>
@endif

<div class="card mb-4">
  <div class="card-body py-3">
    <div class="d-flex flex-wrap gap-2">
      <a href="{{ route('admin.feedback.index') }}"
         class="btn btn-sm {{ $activeStatus ? 'btn-outline-primary' : 'btn-primary' }}">
        {{ __('All') }} <span class="ms-1">{{ $statusCounts->sum() }}</span>
      </a>
      @foreach ($statuses as $status => $label)
        <a href="{{ route('admin.feedback.index', ['status' => $status]) }}"
           class="btn btn-sm {{ $activeStatus === $status ? 'btn-primary' : 'btn-outline-primary' }}">
          {{ $label }} <span class="ms-1">{{ $statusCounts[$status] ?? 0 }}</span>
        </a>
      @endforeach
    </div>
  </div>
</div>

<div class="card">
  <div class="table-responsive">
    <table class="table mb-0 align-middle feedback-table">
      <thead>
        <tr>
          <th>{{ __('Feedback') }}</th>
          <th>{{ __('User') }}</th>
          <th>{{ __('Rating') }}</th>
          <th>{{ __('Status') }}</th>
          <th>{{ __('Created') }}</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($feedback as $item)
          <tr>
            <td>
              <div class="fw-semibold">{{ $item->subject }}</div>
              <div class="small text-muted mb-1">{{ $categories[$item->category] ?? $item->category }}</div>
              <div>{{ $item->message }}</div>
            </td>
            <td>
              <div class="fw-semibold">{{ $item->user?->name ?? __('Deleted user') }}</div>
              <div class="small text-muted">{{ $item->user?->email }}</div>
              @if ($item->reviewer)
                <div class="small text-muted mt-1">
                  {{ __('Reviewed by') }} {{ $item->reviewer->name }}
                </div>
              @endif
            </td>
            <td>{{ $item->rating ? $item->rating.' / 5' : __('No rating') }}</td>
            <td>
              <form method="POST" action="{{ route('admin.feedback.update', $item) }}">
                @csrf
                @method('PATCH')
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                  @foreach ($statuses as $status => $label)
                    <option value="{{ $status }}" @selected($item->status === $status)>{{ $label }}</option>
                  @endforeach
                </select>
              </form>
            </td>
            <td>
              <time class="admin-time" datetime="{{ optional($item->created_at)->toIso8601String() }}">
                {{ optional($item->created_at)->format('d/m/Y H:i') }}
              </time>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" class="text-center text-muted py-4">{{ __('No feedback yet.') }}</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  @if ($feedback->hasPages())
    <div class="card-body border-top">
      {{ $feedback->links() }}
    </div>
  @endif
</div>
@endsection
