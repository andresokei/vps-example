<h2 class="h5">{{ __('report_view.observations') }}</h2>
@if(empty($datos['totales']['respondieron']))
  <p>{{ __('analysis_screen.no_answers') }}</p>
@else
  <p>{{ __('report_view.participation', ['n' => $datos['totales']['respondieron'], 'total' => $datos['totales']['alumnos']]) }}</p>
  <p><strong>{{ __('report_view.no_choices') }}</strong><br>
    {{ count($datos['roles']['aislados']) ? implode(', ', $datos['roles']['aislados']) : __('report_view.none') }}</p>
  <p><strong>{{ __('report_view.majority') }}</strong><br>
    @forelse($datos['mayoria_rechazos'] as $row)
      {{ $row['nombre'] }} · {{ __('report_view.peer_count', ['n' => $row['rechazan'], 'total' => $row['posibles']]) }}<br>
    @empty
      {{ __('report_view.none') }}
    @endforelse
  </p>
@endif
