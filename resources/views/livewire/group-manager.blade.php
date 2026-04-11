<div>
  @if (session()->has('message'))
    <div class="alert alert-success d-flex align-items-center gap-2" role="alert">
      <i class="bi bi-check-circle-fill flex-shrink-0"></i>
      {{ session('message') }}
    </div>
  @endif

  <form wire:submit.prevent="createGroup" class="mb-4">
    <div class="input-group">
      <input type="text"
             wire:model="groupName"
             placeholder="{{ __('New group name') }}"
             class="form-control"
             aria-label="{{ __('New group name') }}">
      <button class="btn btn-primary" type="submit">
        <i class="bi bi-plus-lg"></i>
      </button>
    </div>
  </form>

  <ul class="list-unstyled mb-0">
    @forelse ($groups as $group)
      <li class="d-flex justify-content-between align-items-center py-2 border-bottom">
        <span class="fw-medium">{{ $group->nombre_grupo }}</span>
        <div class="btn-group btn-group-sm">
          <button class="btn btn-outline-secondary"
                  wire:click.prevent="openModal({{ $group->id }})"
                  title="{{ __('Add students') }}">
            <i class="bi bi-person-plus"></i>
          </button>
          <button class="btn btn-outline-danger"
                  wire:click.prevent="deleteGroup({{ $group->id }})"
                  title="{{ __('Delete group') }}">
            <i class="bi bi-trash"></i>
          </button>
        </div>
      </li>
    @empty
      <li class="text-center text-muted py-3" style="font-size:0.875rem;">
        {{ __('No groups created yet.') }}
      </li>
    @endforelse
  </ul>

  <div class="modal fade" id="addStudentsModal"
       tabindex="-1"
       aria-labelledby="addStudentsModalLabel"
       aria-hidden="true"
       wire:ignore.self>
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="addStudentsModalLabel">{{ __('Add students') }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
        </div>

        <div class="modal-body">
          <ul class="nav nav-tabs mb-4" id="addStudentsTabs" role="tablist">
            <li class="nav-item" role="presentation">
              <button class="nav-link"
                      id="csv-tab"
                      data-bs-toggle="tab"
                      data-bs-target="#csv"
                      type="button"
                      role="tab">
                {{ __('Import CSV') }}
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link active"
                      id="manual-tab"
                      data-bs-toggle="tab"
                      data-bs-target="#manual"
                      type="button"
                      role="tab">
                {{ __('Add manually') }}
              </button>
            </li>
          </ul>

          <div class="tab-content">
            <div class="tab-pane fade" id="csv" role="tabpanel">
              <div class="mb-3">
                <label for="csvFileInput" class="form-label">{{ __('Select CSV file') }}</label>
                <input type="file"
                       class="form-control"
                       id="csvFileInput"
                       wire:model="csvFile">
                <div class="form-text">{{ __('Format: one name per line.') }}</div>
              </div>
              @error('csvFile')
                <div class="text-danger small mb-2">
                  <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                </div>
              @enderror
              <div class="d-grid">
                <button class="btn btn-primary"
                        wire:click="uploadCSV"
                        wire:loading.attr="disabled">
                  <span wire:loading.remove wire:target="uploadCSV">
                    <i class="bi bi-upload me-1"></i> {{ __('Import students') }}
                  </span>
                  <span wire:loading wire:target="uploadCSV">
                    <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                    {{ __('Importing...') }}
                  </span>
                </button>
              </div>
            </div>

            <div class="tab-pane fade show active" id="manual" role="tabpanel">
              <form wire:submit.prevent="addStudentFromList">
                <div class="mb-3">
                  <label for="studentNames" class="form-label">{{ __('Names separated by commas') }}</label>
                  <textarea wire:model="studentNames"
                            class="form-control"
                            id="studentNames"
                            rows="3"
                            placeholder="{{ __('E.g.: Juan Perez, Maria Garcia') }}"></textarea>
                  @error('studentNames')
                    <div class="text-danger small mt-1">
                      <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                    </div>
                  @enderror
                </div>
                <div class="d-grid">
                  <button type="submit"
                          class="btn btn-primary"
                          wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="addStudentFromList">
                      <i class="bi bi-plus-lg me-1"></i> {{ __('Add students to group') }}
                    </span>
                    <span wire:loading wire:target="addStudentFromList">
                      <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                      {{ __('Adding...') }}
                    </span>
                  </button>
                </div>
              </form>
            </div>
          </div>

          <div class="mt-4 pt-3 border-top">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h6 class="mb-0 fw-semibold" style="font-size:0.875rem;">{{ __('Students in this group') }}</h6>
              <span class="badge bg-primary rounded-pill"
                    wire:loading.class="d-none"
                    wire:target="loadGroupStudents">
                {{ count($groupStudents ?? []) }}
              </span>
              <span wire:loading wire:target="loadGroupStudents"
                    class="spinner-border spinner-border-sm text-primary"
                    role="status"></span>
            </div>

            @if(!empty($groupStudents) && count($groupStudents) > 0)
              <div class="list-group list-group-flush" style="max-height:240px;overflow-y:auto;">
                @foreach($groupStudents as $student)
                  <div class="list-group-item d-flex justify-content-between align-items-center py-2 px-2">
                    <span style="font-size:0.875rem;">{{ $student->nombre }}</span>
                    <button class="btn btn-sm btn-outline-danger"
                            wire:click="removeStudentFromGroup({{ $student->id }})"
                            wire:loading.attr="disabled">
                      <i class="bi bi-x"></i>
                    </button>
                  </div>
                @endforeach
              </div>
            @else
              <div class="text-center py-3 text-muted" style="font-size:0.855rem;">
                <i class="bi bi-people mb-2 d-block" style="font-size:1.75rem;"></i>
                {{ __('No students in this group.') }}
              </div>
            @endif
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" wire:click="closeModal">{{ __('Close') }}</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="deleteGroupModal"
       tabindex="-1"
       aria-labelledby="deleteGroupModalLabel"
       aria-hidden="true"
       wire:ignore.self>
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title" id="deleteGroupModalLabel">{{ __('Confirm deletion') }}</h5>
          <button type="button"
                  class="btn-close btn-close-white"
                  data-bs-dismiss="modal"
                  aria-label="{{ __('Close') }}"></button>
        </div>
        <div class="modal-body text-center py-4">
          <i class="bi bi-exclamation-triangle text-warning mb-3 d-block" style="font-size:2.5rem;"></i>
          <p class="mb-1">{{ __('Are you sure you want to delete this group?') }}</p>
          <p class="text-danger fw-medium mb-0">{{ __('This action cannot be undone.') }}</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="bi bi-x-lg me-1"></i> {{ __('Cancel') }}
          </button>
          <button type="button"
                  class="btn btn-danger"
                  wire:click="confirmDeleteGroup"
                  wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="confirmDeleteGroup">
              <i class="bi bi-trash me-1"></i> {{ __('Delete group') }}
            </span>
            <span wire:loading wire:target="confirmDeleteGroup">
              <span class="spinner-border spinner-border-sm me-1" role="status"></span>
              {{ __('Deleting...') }}
            </span>
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
document.addEventListener('livewire:init', () => {
  const addModal = new bootstrap.Modal(document.getElementById('addStudentsModal'));
  const deleteModal = new bootstrap.Modal(document.getElementById('deleteGroupModal'));

  Livewire.on('openModal', () => addModal.show());
  Livewire.on('closeModal', () => addModal.hide());
  Livewire.on('openDeleteModal', () => deleteModal.show());
  Livewire.on('closeDeleteModal', () => deleteModal.hide());
});
</script>
@endpush
