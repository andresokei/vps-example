{{-- <div>
    <input type="text" wire:model.debounce.500ms="studentName" placeholder="Escribe el nombre" class="form-control">
    <p>Valor ingresado: {{ $studentName }}</p>
</div> --}}
<div>
    <input type="text" wire:model.debounce.500ms="studentName" placeholder="Escribe el nombre" class="form-control">
    <p>Valor ingresado: {{ $studentName }}</p>

    <button wire:click="testAction">Probar acción</button>
</div>
