<div class="row g-3 mb-4">
  @foreach(['leaders' => 'Lideres', 'puentes' => 'Puentes', 'aislados' => 'Aislados', 'cohesivos' => 'Cohesivos'] as $key => $label)
    <div class="col-md-3">
      <div class="card shadow-sm h-100">
        <div class="card-header text-center">{{ $label }}</div>
        <div class="card-body">
          <ul class="list-unstyled mb-0">
            @forelse($datos['roles'][$key] ?? [] as $name)
              <li>{{ $name }}</li>
            @empty
              <li class="text-muted">-</li>
            @endforelse
          </ul>
        </div>
      </div>
    </div>
  @endforeach
</div>
