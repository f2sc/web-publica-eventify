@extends('layouts.admin')

@section('title', 'Suscriptores')

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">📬 Suscriptores Newsletter</h1>
        <p style="color:#6b7280;font-size:.875rem;margin-top:.2rem">Lista de personas suscritas al newsletter de Eventify.</p>
    </div>
</div>

{{-- Tarjetas de resumen --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:1.5rem">
    <div class="card" style="text-align:center">
        <div style="font-size:1.4rem;font-weight:800;color:#374151">{{ $totales['total'] }}</div>
        <div style="font-size:.8rem;color:#9ca3af;margin-top:.25rem">Total</div>
    </div>
    <div class="card" style="text-align:center">
        <div style="font-size:1.4rem;font-weight:800;color:#10b981">{{ $totales['confirmados'] }}</div>
        <div style="font-size:.8rem;color:#9ca3af;margin-top:.25rem">Confirmados</div>
    </div>
    <div class="card" style="text-align:center">
        <div style="font-size:1.4rem;font-weight:800;color:#f59e0b">{{ $totales['pendientes'] }}</div>
        <div style="font-size:.8rem;color:#9ca3af;margin-top:.25rem">Pendientes</div>
    </div>
    <div class="card" style="text-align:center">
        <div style="font-size:1.4rem;font-weight:800;color:#ef4444">{{ $totales['bajas'] }}</div>
        <div style="font-size:.8rem;color:#9ca3af;margin-top:.25rem">Bajas</div>
    </div>
</div>

{{-- Filtros --}}
<form method="GET" style="display:flex;gap:.75rem;flex-wrap:wrap;margin-bottom:1.25rem;align-items:flex-end">
    <input type="text" name="q" value="{{ request('q') }}"
           placeholder="Buscar por nombre o email…"
           class="form-control" style="max-width:280px">
    <select name="estado" class="form-control" style="max-width:160px">
        <option value="">Todos</option>
        <option value="confirmado" {{ request('estado') === 'confirmado' ? 'selected' : '' }}>Confirmados</option>
        <option value="pendiente"  {{ request('estado') === 'pendiente'  ? 'selected' : '' }}>Pendientes</option>
        <option value="baja"       {{ request('estado') === 'baja'       ? 'selected' : '' }}>Bajas</option>
    </select>
    <button type="submit" class="btn btn-secondary">Filtrar</button>
    @if(request('q') || request('estado'))
    <a href="{{ route('admin.suscriptores.index') }}" class="btn btn-secondary">✕ Limpiar</a>
    @endif
</form>

{{-- Tabla --}}
<div class="card" style="padding:0;overflow-x:auto">
    <table class="table">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Email</th>
                <th>Fuente</th>
                <th>Estado</th>
                <th>Suscripción</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($suscriptores as $sus)
            <tr>
                <td style="font-weight:600;font-size:.875rem">{{ $sus->nombre }}</td>
                <td style="font-size:.875rem;color:#6b7280">{{ $sus->email }}</td>
                <td>
                    @if($sus->fuente)
                    <span class="badge badge-gray" style="font-size:.72rem">{{ $sus->fuente }}</span>
                    @else
                    <span style="color:#d1d5db">—</span>
                    @endif
                </td>
                <td>
                    @if($sus->unsubscribed_at)
                    <span class="badge" style="background:#fee2e2;color:#991b1b">Baja</span>
                    @elseif($sus->confirmado)
                    <span class="badge badge-green">✓ Confirmado</span>
                    @else
                    <span class="badge" style="background:#fef3c7;color:#92400e">Pendiente</span>
                    @endif
                </td>
                <td style="font-size:.8rem;color:#6b7280;white-space:nowrap">
                    {{ $sus->created_at->format('d/m/Y H:i') }}
                    @if($sus->unsubscribed_at)
                    <br><span style="color:#ef4444">Baja: {{ $sus->unsubscribed_at->format('d/m/Y H:i') }}</span>
                    @endif
                </td>
                <td style="text-align:right">
                    <form method="POST" action="{{ route('admin.suscriptores.destroy', $sus) }}"
                          onsubmit="return confirm('¿Eliminar este suscriptor?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-secondary"
                                style="padding:.3rem .6rem;font-size:.75rem;color:#ef4444;border-color:#fca5a5">
                            Eliminar
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align:center;padding:2.5rem;color:#9ca3af">
                    Sin suscriptores todavía.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if($suscriptores->hasPages())
    <div style="padding:1rem;border-top:1px solid #e5e7eb">{{ $suscriptores->links() }}</div>
    @endif
</div>

@endsection
