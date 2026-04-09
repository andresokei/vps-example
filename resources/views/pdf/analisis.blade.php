<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
  body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #222; margin: 0; padding: 20px; }
  h1 { font-size: 18px; margin-bottom: 2px; color: #1a3a5c; }
  h2 { font-size: 13px; margin: 18px 0 6px; color: #1a3a5c; border-bottom: 1px solid #ccc; padding-bottom: 3px; }
  .meta { color: #555; font-size: 10px; margin-bottom: 14px; }
  .metrics { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
  .metrics td { padding: 5px 10px; border: 1px solid #ddd; }
  .metrics td:first-child { font-weight: bold; background: #f5f7fa; width: 40%; }
  table.data { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
  table.data th { background: #1a3a5c; color: #fff; padding: 5px 8px; text-align: left; font-size: 10px; }
  table.data td { padding: 4px 8px; border-bottom: 1px solid #eee; font-size: 10px; }
  table.data tr:nth-child(even) td { background: #f8f9fa; }
  .roles { width: 100%; border-collapse: collapse; }
  .roles td { vertical-align: top; padding: 6px 10px; border: 1px solid #ddd; width: 25%; }
  .roles td strong { display: block; margin-bottom: 4px; color: #1a3a5c; }
  .badge { display: inline-block; padding: 2px 7px; border-radius: 3px; font-size: 9px; }
  .badge-warning  { background: #fff3cd; color: #856404; }
  .badge-info     { background: #cff4fc; color: #0c5460; }
  .badge-success  { background: #d1e7dd; color: #0a3622; }
  .footer { margin-top: 30px; font-size: 9px; color: #aaa; text-align: right; }
</style>
</head>
<body>

<h1>Reporte de Análisis Sociométrico</h1>
<div class="meta">
  Grupo: <strong>{{ $grupo }}</strong> &nbsp;|&nbsp;
  Test: <strong>{{ $test }}</strong> &nbsp;|&nbsp;
  Generado: {{ now()->format('d/m/Y H:i') }}
</div>

{{-- ── Métricas de red ── --}}
<h2>Métricas de Red</h2>
<table class="metrics">
  <tr><td>Participación</td><td>{{ number_format(($analisis['participation_rate'] ?? 0) * 100, 0) }}%</td></tr>
  <tr><td>Densidad</td><td>{{ number_format($analisis['density'] ?? 0, 4) }}</td></tr>
  <tr><td>Polarización</td><td>{{ number_format($analisis['polarization'] ?? 0, 4) }}</td></tr>
  <tr><td>Reciprocidad</td><td>{{ number_format(($analisis['reciprocity'] ?? 0) * 100, 0) }}%</td></tr>
  <tr><td>Total alumnos</td><td>{{ $analisis['totales']['alumnos'] ?? 0 }}</td></tr>
  <tr><td>Respondieron</td><td>{{ $analisis['totales']['respondieron'] ?? 0 }}</td></tr>
  <tr><td>Total relaciones</td><td>{{ $analisis['totales']['relaciones'] ?? 0 }}</td></tr>
  <tr><td>Preferencias</td><td>{{ $analisis['totales']['preferencias'] ?? 0 }}</td></tr>
  <tr><td>Rechazos</td><td>{{ $analisis['totales']['rechazos'] ?? 0 }}</td></tr>
</table>

{{-- ── Centralidades ── --}}
<h2>Tabla de Centralidades</h2>
<table class="data">
  <thead>
    <tr>
      <th>Alumno</th><th>In-Degree</th><th>Out-Degree</th><th>Betweenness</th><th>Closeness</th>
    </tr>
  </thead>
  <tbody>
    @forelse($analisis['centralities'] ?? [] as $c)
      <tr>
        <td>{{ $c['name'] }}</td>
        <td>{{ $c['inDegree'] }}</td>
        <td>{{ $c['outDegree'] }}</td>
        <td>{{ number_format($c['betweenness'], 2) }}</td>
        <td>{{ number_format($c['closeness'], 2) }}</td>
      </tr>
    @empty
      <tr><td colspan="5">Sin datos.</td></tr>
    @endforelse
  </tbody>
</table>

{{-- ── Roles ── --}}
<h2>Roles Detectados</h2>
<table class="roles">
  @foreach(['leaders' => 'Líderes', 'puentes' => 'Puentes', 'aislados' => 'Aislados', 'cohesivos' => 'Grupo Cohesivo'] as $key => $label)
  <td>
    <strong>{{ $label }}</strong>
    @forelse($analisis['roles'][$key] ?? [] as $name)
      <div>{{ $name }}</div>
    @empty
      <div style="color:#999">—</div>
    @endforelse
  </td>
  @endforeach
</table>

{{-- ── Comunidades ── --}}
<h2>Comunidades Detectadas</h2>
@forelse($analisis['communities'] ?? [] as $i => $comunidad)
  <div><strong>Cluster {{ $i + 1 }}:</strong> {{ implode(', ', $comunidad) }}</div>
@empty
  <div style="color:#999">No se detectaron subgrupos.</div>
@endforelse

{{-- ── Preferencias recibidas ── --}}
@if(!empty($analisis['preferencias']['labels']))
<h2>Preferencias Recibidas</h2>
<table class="data">
  <thead><tr><th>Alumno</th><th>Veces elegido</th></tr></thead>
  <tbody>
    @foreach($analisis['preferencias']['labels'] as $i => $nombre)
      <tr><td>{{ $nombre }}</td><td>{{ $analisis['preferencias']['data'][$i] ?? 0 }}</td></tr>
    @endforeach
  </tbody>
</table>
@endif

{{-- ── Rechazos recibidos ── --}}
@if(!empty($analisis['rechazos']['labels']))
<h2>Rechazos Recibidos</h2>
<table class="data">
  <thead><tr><th>Alumno</th><th>Veces rechazado</th></tr></thead>
  <tbody>
    @foreach($analisis['rechazos']['labels'] as $i => $nombre)
      <tr><td>{{ $nombre }}</td><td>{{ $analisis['rechazos']['data'][$i] ?? 0 }}</td></tr>
    @endforeach
  </tbody>
</table>
@endif

<div class="footer">Generado por la plataforma sociométrica &mdash; {{ now()->format('d/m/Y') }}</div>
</body>
</html>
