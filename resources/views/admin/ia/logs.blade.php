@extends('layouts.admin')

@section('title', 'Logs IA')

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">✦ Logs de generación IA</h1>
        <p style="color:#6b7280;font-size:.875rem;margin-top:.2rem">Historial de llamadas y costes estimados.</p>
    </div>
    <a href="{{ route('admin.ia.config') }}" class="btn btn-secondary">⚙ Configuración</a>
</div>

{{-- Tarjetas de resumen --}}
<div class="logs-stats-grid" style="display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;margin-bottom:1.5rem">
    <div class="card" style="text-align:center">
        <div style="font-size:1.4rem;font-weight:800;color:#6c3fc5">${{ number_format($totalMes, 4) }}</div>
        <div style="font-size:.8rem;color:#9ca3af;margin-top:.25rem">Coste este mes</div>
    </div>
    <div class="card" style="text-align:center">
        <div style="font-size:1.4rem;font-weight:800;color:#6c3fc5">${{ number_format($totalSemana, 4) }}</div>
        <div style="font-size:.8rem;color:#9ca3af;margin-top:.25rem">Coste esta semana</div>
    </div>
    <div class="card" style="text-align:center">
        <div style="font-size:1.4rem;font-weight:800;color:#374151">{{ $totalGeneraciones }}</div>
        <div style="font-size:.8rem;color:#9ca3af;margin-top:.25rem">Generaciones totales</div>
    </div>
</div>

{{-- Tabla de logs --}}
<div class="card" style="padding:0;overflow-x:auto">
    <table class="table">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Artículo</th>
                <th>Tipo</th>
                <th>Proveedor / Modelo</th>
                <th style="text-align:right">T. entrada</th>
                <th style="text-align:right">T. salida</th>
                <th style="text-align:right">Coste</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
            <tr>
                <td style="white-space:nowrap;color:#6b7280;font-size:.82rem">
                    {{ $log->created_at->format('d/m H:i') }}
                </td>
                <td>
                    @if($log->articulo)
                    <a href="{{ route('admin.articulos.edit', $log->articulo) }}" style="color:#6c3fc5;font-size:.85rem;font-weight:600">
                        {{ Str::limit($log->articulo->titulo, 40) }}
                    </a>
                    @else
                    <span style="color:#d1d5db">—</span>
                    @endif
                </td>
                <td>
                    @php
                    $typeLabels = ['full_article' => 'Artículo completo', 'image' => 'Imagen', 'field_regen' => 'Campo: ' . ($log->field_name ?? '')];
                    @endphp
                    <span class="badge badge-gray">{{ $typeLabels[$log->type] ?? $log->type }}</span>
                </td>
                <td style="font-size:.82rem;color:#374151">
                    {{ $log->provider }}<br>
                    <span style="color:#9ca3af;font-size:.75rem">{{ Str::limit($log->model, 24) }}</span>
                </td>
                <td style="text-align:right;font-size:.82rem">{{ $log->input_tokens ? number_format($log->input_tokens) : '—' }}</td>
                <td style="text-align:right;font-size:.82rem">{{ $log->output_tokens ? number_format($log->output_tokens) : '—' }}</td>
                <td style="text-align:right;font-weight:700;font-size:.85rem">
                    @if($log->cost_usd)
                    ${{ number_format($log->cost_usd, 4) }}
                    @else <span style="color:#d1d5db">—</span>
                    @endif
                </td>
                <td>
                    @if($log->status === 'ok')
                    <span class="badge badge-green">✓ ok</span>
                    @else
                    <span class="badge" style="background:#fee2e2;color:#991b1b" title="{{ $log->error_message }}">✗ error</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align:center;padding:2.5rem;color:#9ca3af">
                    Sin registros aún.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if($logs->hasPages())
    <div style="padding:1rem;border-top:1px solid #e5e7eb">{{ $logs->links() }}</div>
    @endif
</div>

@endsection
