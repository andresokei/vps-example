@extends('layouts.dashboard')

@section('title', 'Feedback')

@section('content')
<div class="page-header">
  <h1 class="page-title">{{ __('Feedback') }}</h1>
  <p class="page-subtitle">{{ __('Tell us what is working, what is confusing, or what would make Sociogram better for you.') }}</p>
</div>

@if (session('status'))
  <div class="alert alert-success">
    <i class="bi bi-check-circle me-1"></i>{{ session('status') }}
  </div>
@endif

<div class="row g-4">
  <div class="col-xl-7">
    <div class="card">
      <div class="card-header">
        <h5 class="card-title"><i class="bi bi-chat-dots me-2 text-primary"></i>{{ __('Send feedback') }}</h5>
      </div>
      <div class="card-body">
        <form method="POST" action="{{ route('feedback.store') }}" class="feedback-form">
          @csrf

          <div class="row g-3">
            <div class="col-md-6">
              <label for="category" class="form-label">{{ __('Type') }}</label>
              <select id="category" name="category" class="form-select @error('category') is-invalid @enderror" required>
                @foreach ($categories as $value => $label)
                  <option value="{{ $value }}" @selected(old('category', 'general') === $value)>{{ $label }}</option>
                @endforeach
              </select>
              @error('category') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
              <label for="rating" class="form-label">{{ __('Rating') }}</label>
              <select id="rating" name="rating" class="form-select @error('rating') is-invalid @enderror">
                <option value="">{{ __('No rating') }}</option>
                @for ($rating = 5; $rating >= 1; $rating--)
                  <option value="{{ $rating }}" @selected((string) old('rating') === (string) $rating)>
                    {{ $rating }} / 5
                  </option>
                @endfor
              </select>
              @error('rating') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-12">
              <label for="subject" class="form-label">{{ __('Subject') }}</label>
              <input id="subject" name="subject" type="text" maxlength="120"
                     value="{{ old('subject') }}"
                     class="form-control @error('subject') is-invalid @enderror"
                     required>
              @error('subject') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-12">
              <label for="message" class="form-label">{{ __('Message') }}</label>
              <textarea id="message" name="message" rows="7"
                        class="form-control @error('message') is-invalid @enderror"
                        required>{{ old('message') }}</textarea>
              @error('message') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
          </div>

          <div class="d-flex justify-content-end mt-4">
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-send me-1"></i>{{ __('Send feedback') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="col-xl-5">
    <div class="card h-100">
      <div class="card-header">
        <h5 class="card-title"><i class="bi bi-clock-history me-2 text-primary"></i>{{ __('Your recent feedback') }}</h5>
      </div>
      <div class="card-body">
        @forelse ($recentFeedback as $item)
          <div class="admin-list-row align-items-start">
            <div>
              <div class="fw-semibold">{{ $item->subject }}</div>
              <div class="small text-muted">{{ $categories[$item->category] ?? $item->category }}</div>
              <div class="small mt-1">{{ Str::limit($item->message, 120) }}</div>
            </div>
            <span class="badge text-bg-light border">{{ $statuses[$item->status] ?? $item->status }}</span>
          </div>
        @empty
          <div class="text-muted">{{ __('No feedback sent yet.') }}</div>
        @endforelse
      </div>
    </div>
  </div>
</div>
@endsection
