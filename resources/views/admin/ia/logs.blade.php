@extends('layouts.admin')

@section('title', 'Logs IA')

@section('content')
<div class="admin-header" style="display:flex;justify-content:space-between;align-items:flex-start">
    <div>
        <h1>✦ Logs de generación IA</h1>
        <p style="color:#6b7280;margin-top:.25rem">Historial de llamadas y costes estimados.</p>
    </div>
    <a href="{{ route('admin.ia.config') }}" class="btn btn-secondary">⚙ Configuración</a>
</div>

{{-- Resumen de costes --}}
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;margin-bottom:1.5rem">
    <div class="admin-card" style="text-align:center;padding:1.25rem">
        <div style="font-size:1.5rem;font-weight:700;color:#6c3fc5">${{ number_format($totalMes, 4) }}</div>
        <div style="font-size:.8rem;color:#9ca3af;margin-top:.25rem">Coste este mes</div>
    </div>
    <div class="admin-card" style="text-align:center;padding:1.25rem">
        <div style="font-size:1.5rem;font-weight:700;color:#6c3fc5">${{ number_format($totalSemana, 4) }}</div>
        <div style="font-size:.8rem;color:#9ca3af;margin-top:.25rem">Coste esta semana</div>
    </div>
    <div class="admin-card" style="text-align:center;padding:1.25rem">
        <div style="font-size:1.5rem;font-weight:700;color:#374151">{{ $totalGeneraciones }}</div>
        <div style="font-size:.8rem;color:#9ca3af;margin-top:.25rem">Generaciones totales</div>
    </div>
</div>

<div class="admin-card">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Artículo</th>
                <th>Tipo</th>
                <th>Proveedor / Modelo</th>
                <th>Tokens in</th>
                <th>Tokens out</th>
                <th>Coste USD</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
            <tr>
                <td style="white-space:nowrap;font-size:.8rem;color:#6b7280">
                    {{ $log->created_at->format('d/m H:i') }}
                </td>
                <td>
                    @if($log->articulo)
                    <a href="{{ route('admin.articulos.edit', $log->articulo) }}" style="font-size:.85rem;color:#6c3fc5">
                        {{ Str::limit($log->articulo->titulo, 40) }}
                    </a>
                    @else
                    <span style="color:#9ca3af;font-size:.8rem">—</span>
                    @endif
                </td>
                <td>
                    @php
                    $typeLabels = ['full_article' => 'Artículo completo', 'image' => 'Imagen', 'field_regen' => 'Campo: ' . ($log->field_name ?? '')];
                    @endphp
                    <span style="font-size:.8rem">{{ $typeLabels[$log->type] ?? $log->type }}</span>
                </td>
                <td style="font-size:.8rem;color:#374151">
                    {{ $log->provider }} / {{ Str::limit($log->model, 20) }}
                </td>
                <td style="font-size:.8rem;text-align:right">{{ $log->input_tokens ? number_format($log->input_tokens) : '—' }}</td>
                <td style="font-size:.8rem;text-align:right">{{ $log->output_tokens ? number_format($log->output_tokens) : '—' }}</td>
                <td style="font-size:.85rem;font-weight:600;text-align:right">
                    @if($log->cost_usd)
                    ${{ number_format($log->cost_usd, 4) }}
                    @else —
                    @endif
                </td>
                <td>
                    @if($log->status === 'ok')
                    <span style="color:#10b981;font-size:.8rem">✓ ok</span>
                    @else
                    <span style="color:#ef4444;font-size:.8rem" title="{{ $log->error_message }}">✗ error</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="8" style="text-align:center;padding:2rem;color:#9ca3af">Sin registros aún.</td></tr>
            @endforelse
        </tbody>
    </table>

    @if($logs->hasPages())
    <div style="margin-top:1rem">{{ $logs->links() }}</div>
    @endif
</div>
@endsection
