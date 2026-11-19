<div x-data="{ mode: 'manual' }">
  <div class="btn-group btn-group-sm mb-3" role="group" aria-label="{{ __('Add students') }}">
    <button type="button" class="btn btn-outline-primary" :class="{ active: mode === 'manual' }"
            @click="mode = 'manual'" :aria-pressed="mode === 'manual'" aria-controls="manual">{{ __('Add manually') }}</button>
    <button type="button" class="btn btn-outline-primary" :class="{ active: mode === 'csv' }"
            @click="mode = 'csv'" :aria-pressed="mode === 'csv'" aria-controls="csv">{{ __('Import CSV') }}</button>
  </div>
  <div id="csv" x-show="mode === 'csv'" style="display:none">
    <div class="mb-3">
      <label for="csvFileInput" class="form-label">{{ __('Select CSV file') }}</label>
      <input type="file"
             class="form-control"
             id="csvFileInput" accept=".csv,.txt"
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

  <div id="manual" x-show="mode === 'manual'">
    <form wire:submit.prevent="addStudentFromList">
      <div class="mb-3">
        <label for="studentNames" class="form-label">{{ __('classroom.names') }}</label>
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
