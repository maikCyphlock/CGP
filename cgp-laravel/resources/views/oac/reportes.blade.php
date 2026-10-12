@extends('layouts.panel')

@section('titulo', 'Reportes y estadísticas')

@section('contenido')
<form method="GET" class="card mb-4"><div class="card-body d-flex flex-wrap gap-3 align-items-end">
  <div><label class="form-label small mb-1">Desde</label><input type="date" name="desde" value="{{ $desde }}" class="form-control"></div>
  <div><label class="form-label small mb-1">Hasta</label><input type="date" name="hasta" value="{{ $hasta }}" class="form-control"></div>
  <button class="btn btn-primary">Aplicar</button>
  <a class="btn btn-outline-secondary ms-auto" href="{{ request()->fullUrlWithQuery(['formato' => 'csv', 'desde' => $desde, 'hasta' => $hasta]) }}"><i class="fa-solid fa-file-csv me-1"></i> Descargar CSV</a>
</div></form>

<div class="stat-strip">
  <a><div class="stat-label">Recibidos en el período</div><div class="stat-value">{{ $total }}</div></a>
  <a><div class="stat-label">Cerrados</div><div class="stat-value">{{ $cerrados }}</div></a>
  <a><div class="stat-label">Eficiencia de cierre</div><div class="stat-value">{{ $eficiencia }}%</div></a>
  <a><div class="stat-label">Sin clasificar</div><div class="stat-value {{ $sinClasificar ? 'accent' : '' }}">{{ $sinClasificar }}</div></a>
</div>

<div class="row g-4 mb-4">
  @foreach ([
    'Por estatus' => $porEstatus->pluck('n', 'name'),
    'Por tipo de trámite' => $porTipo,
    'Por irregularidad' => $porIrregularidad,
    'Recibidos por mes' => $porMes,
  ] as $titulo => $filas)
    @php $max = max(1, collect($filas)->max()); @endphp
    <div class="col-lg-6"><div class="card h-100">
      <div class="card-header fw-bold text-primary-cgp">{{ $titulo }}</div>
      <div class="card-body">
        @forelse ($filas as $nombre => $n)
          <div class="mb-2">
            <div class="d-flex justify-content-between small"><span>{{ $nombre }}</span><strong>{{ $n }}</strong></div>
            <div class="progress" style="height:8px"><div class="progress-bar" style="width: {{ round(100 * $n / $max) }}%"></div></div>
          </div>
        @empty
          <p class="text-muted mb-0">Sin datos en el período.</p>
        @endforelse
      </div>
    </div></div>
  @endforeach
</div>

<div class="card">
  <div class="card-header fw-bold text-primary-cgp">Soportes físicos vencidos <span class="badge bg-secondary ms-1">{{ $vencidos->count() }}</span></div>
  <div class="table-responsive"><table class="table table-hover mb-0">
    <thead><tr><th class="ps-4">Expediente</th><th>Venció el</th></tr></thead>
    <tbody>
      @forelse ($vencidos as $v)
        <tr class="fila-link" onclick="location.href='{{ route('oac.expedientes.show', $v->id) }}'">
          <td class="ps-4">{{ $v->case_number }}</td><td>{{ \Illuminate\Support\Carbon::parse($v->physical_docs_deadline)->format('d/m/Y') }}</td>
        </tr>
      @empty
        <tr><td colspan="2" class="ps-4 text-muted">No hay expedientes con soportes vencidos.</td></tr>
      @endforelse
    </tbody>
  </table></div>
</div>
@endsection
