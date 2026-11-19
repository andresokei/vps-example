<div>
  <div class="page-header">
    <a class="d-inline-block mb-2" href="{{ route('grupos.index') }}">{{ __('My Groups') }}</a>
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
      <div>
        <h1 class="page-title text-break">{{ $grupo->nombre_grupo }}</h1>
        <p class="page-subtitle mb-0">{{ trans_choice('classroom.students', count($groupStudents)) }}</p>
      </div>
      @if(auth()->user()->hasAnyRole(['profesor', 'admin']))
        <a class="btn btn-outline-primary" href="{{ route('analisis', ['grupo' => $grupo->id]) }}">{{ __('View analysis') }}</a>
      @endif
    </div>
  </div>

  @if(session()->has('message'))
    <div role="status" class="alert alert-success">{{ session('message') }}</div>
  @endif

  <div class="row g-3 align-items-start">
    <div class="col-12 col-lg-7">
      <section class="card" aria-labelledby="group-students">
        <div class="card-body">
          <h2 id="group-students" class="h5 mb-3">{{ __('Students in this group') }}</h2>
          <ul class="list-group list-group-flush">
            @forelse($groupStudents as $student)
              <li class="list-group-item d-flex align-items-center justify-content-between gap-2 px-0" wire:key="member-{{ $student->id }}">
                <span class="text-break">{{ $student->nombre }}</span>
                @unless(session()->has('impersonator_id'))
                  <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="removeStudentFromGroup({{ $student->id }})"
                          wire:confirm="{{ __('classroom.confirm_remove', ['name' => $student->nombre]) }}" wire:loading.attr="disabled">
                    {{ __('classroom.remove') }}<span class="visually-hidden"> {{ $student->nombre }}</span>
                  </button>
                @endunless
              </li>
            @empty
              <li class="list-group-item px-0 border-0 text-muted">{{ __('No students in this group.') }}</li>
            @endforelse
          </ul>
        </div>
      </section>
    </div>
    @unless(session()->has('impersonator_id'))
      <div class="col-12 col-lg-5">
        <section class="card card-body" aria-labelledby="add-students">
          <h2 class="h5 mb-3" id="add-students">{{ __('Add students') }}</h2>
          @include('livewire.partials.group-import')
        </section>
      </div>
    @endunless
  </div>
</div>
