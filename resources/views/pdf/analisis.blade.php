<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
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

<h1>{{ __('Sociometric Analysis Report') }}</h1>
<div class="meta">
  {{ __('Group') }}: <strong>{{ $grupo }}</strong> &nbsp;|&nbsp;
  {{ __('Test') }}: <strong>{{ $test }}</strong> &nbsp;|&nbsp;
  {{ __('Generated:') }} {{ now()->format('d/m/Y H:i') }}
</div>

{{-- ── Métricas de red ── --}}
<h2>{{ __('Network Metrics') }}</h2>
<table class="metrics">
  <tr><td>{{ __('Participation') }}</td><td>{{ number_format(($analisis['participation_rate'] ?? 0) * 100, 0) }}%</td></tr>
  <tr><td>{{ __('Density') }}</td><td>{{ number_format($analisis['density'] ?? 0, 4) }}</td></tr>
  <tr><td>{{ __('Polarization') }}</td><td>{{ number_format($analisis['polarization'] ?? 0, 4) }}</td></tr>
  <tr><td>{{ __('Reciprocity') }}</td><td>{{ number_format(($analisis['reciprocity'] ?? 0) * 100, 0) }}%</td></tr>
  <tr><td>{{ __('Total students') }}</td><td>{{ $analisis['totales']['alumnos'] ?? 0 }}</td></tr>
  <tr><td>{{ __('Responded') }}</td><td>{{ $analisis['totales']['respondieron'] ?? 0 }}</td></tr>
  <tr><td>{{ __('Total relations') }}</td><td>{{ $analisis['totales']['relaciones'] ?? 0 }}</td></tr>
  <tr><td>{{ __('Preferences') }}</td><td>{{ $analisis['totales']['preferencias'] ?? 0 }}</td></tr>
  <tr><td>{{ __('Rejections') }}</td><td>{{ $analisis['totales']['rechazos'] ?? 0 }}</td></tr>
</table>

{{-- ── Centralidades ── --}}
<h2>{{ __('Centrality Table') }}</h2>
<table class="data">
  <thead>
    <tr>
      <th>{{ __('Student') }}</th><th>In-Degree</th><th>Out-Degree</th><th>Betweenness</th><th>Closeness</th>
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
      <tr><td colspan="5">{{ __('No data.') }}</td></tr>
    @endforelse
  </tbody>
</table>

{{-- ── Roles ── --}}
<h2>{{ __('Detected Roles') }}</h2>
<table class="roles">
  @foreach(['leaders' => __('Leaders'), 'puentes' => __('Bridges'), 'aislados' => __('Isolated'), 'cohesivos' => __('Cohesive group')] as $key => $label)
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
<h2>{{ __('Detected Communities') }}</h2>
@forelse($analisis['communities'] ?? [] as $i => $comunidad)
  <div><strong>Cluster {{ $i + 1 }}:</strong> {{ implode(', ', $comunidad) }}</div>
@empty
  <div style="color:#999">{{ __('No subgroups detected.') }}</div>
@endforelse

{{-- ── Preferencias recibidas ── --}}
@if(!empty($analisis['preferencias']['labels']))
<h2>{{ __('Received Preferences') }}</h2>
<table class="data">
  <thead><tr><th>{{ __('Student') }}</th><th>{{ __('Times chosen') }}</th></tr></thead>
  <tbody>
    @foreach($analisis['preferencias']['labels'] as $i => $nombre)
      <tr><td>{{ $nombre }}</td><td>{{ $analisis['preferencias']['data'][$i] ?? 0 }}</td></tr>
    @endforeach
  </tbody>
</table>
@endif

{{-- ── Rechazos recibidos ── --}}
@if(!empty($analisis['rechazos']['labels']))
<h2>{{ __('Received Rejections') }}</h2>
<table class="data">
  <thead><tr><th>{{ __('Student') }}</th><th>{{ __('Times rejected') }}</th></tr></thead>
  <tbody>
    @foreach($analisis['rechazos']['labels'] as $i => $nombre)
      <tr><td>{{ $nombre }}</td><td>{{ $analisis['rechazos']['data'][$i] ?? 0 }}</td></tr>
    @endforeach
  </tbody>
</table>
@endif

<div class="footer">{{ __('Generated by the sociometric platform') }} &mdash; {{ now()->format('d/m/Y') }}</div>
</body>
</html>
