{{-- resources/views/livewire/partials/historial.blade.php --}}
<div class="card shadow-sm mb-5">
  <div class="card-header">Historial Temporal</div>
  <div class="card-body">
    <input
      type="range"
      min="0"
      max="{{ count($tests)-1 }}"
      wire:model="indiceTest"
      class="form-range"
    >
    <p class="text-center text-muted mt-2">
      {{ $tests[$indiceTest]->created_at->format('d/m/Y') }}
    </p>
  </div>
</div>
