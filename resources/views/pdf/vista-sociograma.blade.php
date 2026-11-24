<!doctype html>
<html lang="{{ app()->getLocale() }}"><head><meta charset="utf-8">
<style>
body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #24364a; }
h1 { font-size: 20px; } h2 { font-size: 15px; }
table { width: 100%; border-collapse: collapse; }
td, th { text-align: left; padding: 4px 8px; border-bottom: 1px solid #dbe1ea; }
th { background: #e7edf2; }
</style></head><body>
@include('pdf.grafo', ['standalone' => true])
@if($graph['links'])
  @php($labels = collect($graph['nodes'])->pluck('label', 'id'))
  <h2>{{ __('Relations') }}</h2>
  <table><thead><tr><th>{{ __('report_view.from') }}</th><th>{{ __('report_view.to') }}</th><th>{{ __('Relations') }}</th></tr></thead>
    <tbody>
    @foreach($graph['links'] as $edge)
      <tr><td>{{ $labels[$edge['source']] }}</td><td>{{ $labels[$edge['target']] }}</td>
      <td style="background-color:{{ $edge['tipo'] === 'preferido' ? '#dbefdf' : '#f9dce1' }}">{{ __('report_view.'.$edge['tipo']) }}</td></tr>
    @endforeach
    </tbody>
  </table>
@endif
</body></html>
