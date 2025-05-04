<div class="row row-cols-1 row-cols-md-2 g-4 mb-4">
  @php
    $cfg = [
      ['title'=>'Recuento de Preferencias','id'=>'graficoPreferencias','labels'=>$datos['preferencias']['labels'],'data'=>$datos['preferencias']['data'],'color'=>'primary','legend'=>'Veces elegido'],
      ['title'=>'Recuento de Rechazos',    'id'=>'graficoRechazos',    'labels'=>$datos['rechazos']['labels'],    'data'=>$datos['rechazos']['data'],    'color'=>'danger', 'legend'=>'Veces rechazado'],
    ];
  @endphp

  @foreach($cfg as $g)
    <div class="col">
      <div class="card shadow-sm h-100">
        <div class="card-header">{{ $g['title'] }}</div>
        <div class="card-body">
          <div class="ratio ratio-16x9">
            <canvas id="{{ $g['id'] }}"></canvas>
          </div>
          <small class="d-block mt-2 text-muted">{{ $g['legend'] }} por estudiante.</small>
        </div>
      </div>
    </div>
  @endforeach
</div>
