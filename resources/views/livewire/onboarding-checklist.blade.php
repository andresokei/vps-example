<div x-data="{ allDone: false, hiding: false }"
     x-on:onboarding-all-done.window="allDone = true; setTimeout(() => { hiding = true; }, 2500)">

  @if ($visible)
  <div class="card mb-4" style="border: 2px solid #6366f1;" x-show="!hiding">

    {{-- Congratulatory overlay --}}
    <template x-if="allDone">
      <div class="card-body py-4 px-4 text-center">
        <i class="bi bi-check-circle-fill text-success d-block mb-2" style="font-size:2.5rem;"></i>
        <h6 class="fw-semibold mb-1" style="color:#4f46e5;">{{ __("You're all set!") }}</h6>
        <p class="text-muted small mb-0">{{ __('Your class is ready. Check the assigned tests below.') }}</p>
      </div>
    </template>

    {{-- Normal checklist content --}}
    <div class="card-body py-3 px-4" x-show="!allDone">

      <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
          <h6 class="mb-1 fw-semibold" style="color:#4f46e5;">
            <i class="bi bi-rocket-takeoff me-2"></i>{{ __('Getting started') }}
          </h6>
          <p class="text-muted small mb-0">{{ __('Follow these steps to start using Sociogram.') }}</p>
          <div class="mt-2">
            <small class="text-muted">
              {{ __(':done de :total completados', ['done' => $completedCount, 'total' => 3]) }}
            </small>
            <div class="progress mt-1" style="height:6px;">
              <div class="progress-bar bg-success" role="progressbar"
                   style="width:{{ ($completedCount / 3) * 100 }}%"
                   aria-valuenow="{{ $completedCount }}" aria-valuemin="0" aria-valuemax="3">
              </div>
            </div>
          </div>
        </div>
        <button wire:click="dismiss"
                class="btn btn-sm btn-outline-secondary"
                style="font-size:0.8rem;">
          {{ __('Dismiss') }}
        </button>
      </div>

      <div class="d-flex flex-column gap-3">

        {{-- Step 1: Create a group --}}
        <div class="d-flex align-items-start gap-3">
          <div class="flex-shrink-0 mt-1">
            @if ($hasGroups)
              <i class="bi bi-check-circle-fill text-success" style="font-size:1.2rem;"></i>
            @else
              <i class="bi bi-circle text-muted" style="font-size:1.2rem;"></i>
            @endif
          </div>
          <div>
            <div class="fw-medium {{ $hasGroups ? 'text-decoration-line-through text-muted' : '' }}" style="font-size:0.9rem;">
              {{ __('Create your first group') }}
            </div>
            <div class="text-muted" style="font-size:0.8rem;">{{ __('Organize your students into class groups.') }}</div>
            @if (!$hasGroups)
              <a href="#group-manager" class="text-decoration-none" style="color:#6366f1;font-size:0.78rem;">
                {{ __('Go there') }} <i class="bi bi-arrow-right"></i>
              </a>
            @endif
          </div>
        </div>

        {{-- Step 2: Add students --}}
        <div class="d-flex align-items-start gap-3">
          <div class="flex-shrink-0 mt-1">
            @if ($hasStudents)
              <i class="bi bi-check-circle-fill text-success" style="font-size:1.2rem;"></i>
            @else
              <i class="bi bi-circle text-muted" style="font-size:1.2rem;"></i>
            @endif
          </div>
          <div>
            <div class="fw-medium {{ $hasStudents ? 'text-decoration-line-through text-muted' : '' }}" style="font-size:0.9rem;">
              {{ __('Add students to the group') }}
            </div>
            <div class="text-muted" style="font-size:0.8rem;">{{ __('Import them by CSV or add them manually.') }}</div>
            @if (!$hasStudents)
              <a href="#group-manager" class="text-decoration-none" style="color:#6366f1;font-size:0.78rem;">
                {{ __('Go there') }} <i class="bi bi-arrow-right"></i>
              </a>
            @endif
          </div>
        </div>

        {{-- Step 3: Assign a test --}}
        <div class="d-flex align-items-start gap-3">
          <div class="flex-shrink-0 mt-1">
            @if ($hasTests)
              <i class="bi bi-check-circle-fill text-success" style="font-size:1.2rem;"></i>
            @else
              <i class="bi bi-circle text-muted" style="font-size:1.2rem;"></i>
            @endif
          </div>
          <div>
            <div class="fw-medium {{ $hasTests ? 'text-decoration-line-through text-muted' : '' }}" style="font-size:0.9rem;">
              {{ __('Assign a test to the group') }}
            </div>
            <div class="text-muted" style="font-size:0.8rem;">{{ __('Start collecting responses from your students.') }}</div>
            @if (!$hasTests)
              <a href="#assign-tests" class="text-decoration-none" style="color:#6366f1;font-size:0.78rem;">
                {{ __('Go there') }} <i class="bi bi-arrow-right"></i>
              </a>
            @endif
          </div>
        </div>

        {{-- Tip: Share the access key --}}
        @if ($hasTests)
          <div class="alert alert-info d-flex align-items-start gap-2 mb-0 py-2 px-3"
               style="font-size:0.82rem;border-radius:8px;">
            <i class="bi bi-share-fill flex-shrink-0 mt-1"></i>
            <div>
              <strong>{{ __('Next step:') }}</strong>
              {{ __('Share the access key with your students. They enter it at') }}
              <code>/test/ingresar</code>
              {{ __('to answer the test anonymously.') }}
            </div>
          </div>
        @endif

      </div>
    </div>
  </div>
  @endif

</div>
