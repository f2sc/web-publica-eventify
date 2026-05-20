{{-- resources/views/admin/calendario/index.blade.php --}}
@extends('layouts.admin')
@section('title', 'Calendario de publicaciones')

@push('head')
<style>
/* ─── Layout ─────────────────────────────────── */
.cal-wrap { display:grid; grid-template-columns:310px 1fr; gap:0; height:calc(100vh - 56px); overflow:hidden; }
.cal-sidebar { overflow-y:auto; border-right:1px solid #e5e7eb; background:#fafafa; padding:1rem; display:flex; flex-direction:column; gap:1rem; }
.cal-main { display:flex; flex-direction:column; overflow:hidden; }

/* ─── Avisos ──────────────────────────────────── */
.cal-avisos { background:#fff7ed; border-bottom:1px solid #fed7aa; padding:.5rem 1rem; font-size:.8rem; color:#c2410c; flex-shrink:0; }
.cal-avisos a { color:#c2410c; font-weight:600; text-decoration:none; margin-right:.75rem; }
.cal-avisos a:hover { text-decoration:underline; }

/* ─── Toolbar ─────────────────────────────────── */
.cal-toolbar { display:flex; align-items:center; gap:.5rem; padding:.6rem 1rem; border-bottom:1px solid #e5e7eb; flex-shrink:0; background:#fff; }
.cal-toolbar h2 { font-size:1rem; font-weight:700; margin:0 auto 0 .5rem; text-transform:capitalize; }
.cal-btn { border:1px solid #e5e7eb; background:#fff; border-radius:6px; padding:.3rem .65rem; font-size:.82rem; cursor:pointer; }
.cal-btn:hover { background:#f3f4f6; }
.cal-btn.active { background:#6366f1; color:#fff; border-color:#6366f1; }

/* ─── Grid ────────────────────────────────────── */
.cal-grid-wrap { flex:1; overflow-y:auto; padding:.5rem; }
.cal-grid { display:grid; grid-template-columns:repeat(7,1fr); gap:2px; }
.cal-day-hdr { font-size:.72rem; font-weight:700; color:#6b7280; text-align:center; padding:.3rem 0; }
.cal-cell { min-height:90px; background:#fff; border:1px solid #f3f4f6; border-radius:6px; padding:.3rem .35rem; }
.cal-cell--today { border-color:#6366f1; background:#eef2ff; }
.cal-cell--empty { background:transparent; border-color:transparent; }
.cal-day-num { font-size:.75rem; font-weight:700; color:#374151; display:block; margin-bottom:.2rem; }
.cal-cell--today .cal-day-num { color:#6366f1; }
.cal-events { display:flex; flex-direction:column; gap:2px; }

/* ─── Event pills ─────────────────────────────── */
.cal-ev { border-radius:4px; padding:2px 5px; font-size:.7rem; cursor:pointer; display:flex; align-items:center; gap:3px; overflow:hidden; }
.cal-ev-title { flex:1; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.cal-ev-time { flex-shrink:0; font-size:.65rem; opacity:.8; }
.cal-ev--pub    { background:#d1fae5; color:#065f46; }
.cal-ev--prog   { background:#dbeafe; color:#1e40af; border:1px dashed #93c5fd; }
.cal-ev--bor    { background:#fef9c3; color:#854d0e; border:1px dashed #fde047; }
.cal-ev--warn   { background:#fee2e2; color:#991b1b; animation:cal-pulse 1.4s ease-in-out infinite; }
@keyframes cal-pulse { 0%,100%{opacity:1} 50%{opacity:.55} }

/* ─── IA Panel ────────────────────────────────── */
.ia-card { background:#f5f3ff; border:1.5px solid #c4b5fd; border-radius:10px; padding:1rem; }
.ia-title { font-size:.85rem; font-weight:700; color:#6d28d9; margin-bottom:.75rem; }
.ia-toggle { display:flex; border:1px solid #ddd6fe; border-radius:8px; overflow:hidden; margin-bottom:.85rem; }
.ia-toggle button { flex:1; padding:.35rem .4rem; background:#fff; border:none; font-size:.75rem; font-weight:600; color:#8b5cf6; cursor:pointer; }
.ia-toggle button.on { background:#7c3aed; color:#fff; }
.ia-lbl { display:block; font-size:.72rem; font-weight:600; color:#6d28d9; margin-bottom:.2rem; }
.ia-input, .ia-select, .ia-ta { width:100%; background:#fff; border:1px solid #ddd6fe; border-radius:6px; padding:.4rem .6rem; font-size:.8rem; color:#374151; box-sizing:border-box; }
.ia-ta { min-height:80px; resize:vertical; }
.ia-row { display:grid; grid-template-columns:1fr 1fr; gap:.5rem; margin-bottom:.5rem; }
.ia-field { margin-bottom:.5rem; }
.ia-btn { width:100%; background:#7c3aed; color:#fff; border:none; border-radius:8px; padding:.5rem; font-size:.82rem; font-weight:700; margin-top:.5rem; cursor:pointer; }
.ia-btn:disabled { opacity:.6; cursor:not-allowed; }
.ia-hint { font-size:.7rem; color:#8b5cf6; margin-top:.35rem; }
.ia-result { margin-top:.75rem; }
.ia-idea-item { display:flex; align-items:flex-start; gap:.4rem; font-size:.78rem; padding:.35rem 0; border-bottom:1px solid #ede9fe; }
.ia-idea-item:last-child { border-bottom:none; }
.ia-idea-add { flex-shrink:0; background:#ede9fe; color:#7c3aed; border:none; border-radius:4px; width:20px; height:20px; font-size:.85rem; cursor:pointer; line-height:1; }
.ia-plan-item { padding:.4rem 0; border-bottom:1px solid #ede9fe; }
.ia-plan-item:last-child { border-bottom:none; }
.ia-plan-orden { font-size:.68rem; font-weight:700; color:#6d28d9; }
.ia-plan-titulo { font-size:.78rem; font-weight:600; color:#374151; }
.ia-plan-desc { font-size:.72rem; color:#6b7280; margin-top:.1rem; }
.ia-plan-actions { display:flex; gap:.5rem; margin-top:.75rem; }
.ia-plan-btn { flex:1; padding:.4rem; font-size:.75rem; font-weight:600; border:none; border-radius:7px; cursor:pointer; }
.ia-plan-add { background:#d1fae5; color:#065f46; }
.ia-plan-prog { background:#6366f1; color:#fff; }

/* ─── Tintero ─────────────────────────────────── */
.tintero-card { background:#fff; border:1px solid #e5e7eb; border-radius:10px; padding:.85rem; }
.tintero-title { font-size:.85rem; font-weight:700; color:#374151; margin-bottom:.5rem; display:flex; justify-content:space-between; align-items:center; }
.tintero-hint { font-size:.7rem; color:#9ca3af; margin-bottom:.6rem; }
.tintero-serie { margin-bottom:.5rem; }
.tintero-serie-hdr { display:flex; align-items:center; gap:.4rem; cursor:pointer; padding:.3rem .4rem; border-radius:6px; background:#f9fafb; }
.tintero-serie-hdr:hover { background:#f3f4f6; }
.tintero-serie-name { font-size:.78rem; font-weight:700; color:#374151; flex:1; }
.tintero-serie-count { font-size:.68rem; color:#9ca3af; }
.tintero-serie-body { padding:.3rem 0 0 .75rem; }
.tintero-art { display:flex; align-items:center; gap:.4rem; padding:.25rem 0; font-size:.75rem; color:#374151; }
.tintero-art-num { font-size:.65rem; color:#9ca3af; width:14px; flex-shrink:0; }
.tintero-art-title { flex:1; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.tintero-badge { font-size:.6rem; padding:1px 5px; border-radius:99px; flex-shrink:0; }
.badge-bor { background:#fef9c3; color:#854d0e; }
.badge-prog { background:#dbeafe; color:#1e40af; }
.badge-prog-warn { background:#fee2e2; color:#991b1b; }
.tintero-art a { color:#6366f1; font-size:.68rem; margin-left:.3rem; text-decoration:none; flex-shrink:0; }
.tintero-sueltos-lbl { font-size:.7rem; font-weight:700; color:#9ca3af; text-transform:uppercase; letter-spacing:.05em; margin:.6rem 0 .3rem; }
.tintero-prog-btn { width:100%; background:#eef2ff; color:#4f46e5; border:1px dashed #a5b4fc; border-radius:7px; padding:.4rem; font-size:.75rem; font-weight:600; cursor:pointer; margin-top:.5rem; }
.tintero-prog-btn:hover { background:#e0e7ff; }

/* ─── Modal ───────────────────────────────────── */
.cal-modal-bg { position:fixed; inset:0; background:rgba(0,0,0,.45); z-index:1000; display:flex; align-items:center; justify-content:center; }
.cal-modal-box { background:#fff; border-radius:12px; padding:1.5rem; width:100%; max-width:480px; max-height:90vh; overflow-y:auto; }
.cal-modal-title { font-size:1rem; font-weight:800; margin-bottom:1rem; }
.cal-modal-lbl { display:block; font-size:.8rem; font-weight:600; color:#374151; margin-bottom:.25rem; }
.cal-modal-input { width:100%; border:1px solid #e5e7eb; border-radius:6px; padding:.45rem .65rem; font-size:.85rem; box-sizing:border-box; margin-bottom:.75rem; }
.cal-cad-options { display:flex; gap:.4rem; margin-bottom:.75rem; }
.cal-cad-opt { padding:.35rem .65rem; border:1px solid #e5e7eb; border-radius:6px; font-size:.78rem; cursor:pointer; background:#fff; }
.cal-cad-opt.on { background:#6366f1; color:#fff; border-color:#6366f1; }
.cal-sub-input { width:100%; border:1px solid #e5e7eb; border-radius:6px; padding:.4rem .6rem; font-size:.82rem; box-sizing:border-box; margin-bottom:.5rem; }
.cal-preview-title { font-size:.78rem; font-weight:700; color:#374151; margin:.75rem 0 .4rem; }
.cal-preview-item { font-size:.75rem; color:#374151; padding:.2rem 0; border-bottom:1px solid #f3f4f6; display:flex; gap:.5rem; }
.cal-preview-item .warn { color:#ef4444; font-size:.65rem; }
.cal-modal-warn { font-size:.75rem; color:#92400e; background:#fef3c7; border-radius:6px; padding:.5rem .75rem; margin-top:.75rem; }
.cal-modal-actions { display:flex; gap:.75rem; margin-top:1rem; }
.cal-modal-ok { flex:1; background:#6366f1; color:#fff; border:none; border-radius:8px; padding:.55rem; font-size:.85rem; font-weight:700; cursor:pointer; }
.cal-modal-cancel { padding:.55rem 1rem; background:#fff; border:1px solid #e5e7eb; border-radius:8px; font-size:.85rem; cursor:pointer; }

/* ─── Popover ─────────────────────────────────── */
.cal-popover { position:fixed; z-index:999; background:#fff; border:1px solid #e5e7eb; border-radius:10px; box-shadow:0 8px 32px rgba(0,0,0,.12); padding:1rem; width:260px; }
.cal-pop-title { font-weight:700; font-size:.88rem; margin-bottom:.5rem; }
.cal-pop-meta { font-size:.75rem; color:#6b7280; margin-bottom:.75rem; }
.cal-pop-actions { display:flex; flex-direction:column; gap:.35rem; }
.cal-pop-btn { display:block; text-align:left; width:100%; background:#f9fafb; border:1px solid #e5e7eb; border-radius:6px; padding:.4rem .65rem; font-size:.78rem; cursor:pointer; text-decoration:none; color:#374151; }
.cal-pop-btn:hover { background:#f3f4f6; }
.cal-pop-btn.danger { background:#fef2f2; border-color:#fecaca; color:#dc2626; }
</style>
@endpush

@section('content')
<div class="cal-wrap">

  {{-- ═══ SIDEBAR ═══════════════════════════════════ --}}
  <aside class="cal-sidebar">

    {{-- IA PANEL --}}
    <div class="ia-card">
      <div class="ia-title">✦ IA — Planificar contenido</div>
      <div class="ia-toggle">
        <button id="ia-mode-single" class="on" onclick="setIaMode('single')">Artículo único</button>
        <button id="ia-mode-serie" onclick="setIaMode('serie')">Serie de artículos</button>
      </div>

      {{-- Modo: artículo único --}}
      <div id="ia-panel-single">
        <div class="ia-field">
          <label class="ia-lbl">Categoría</label>
          <select id="ia-cat-s" class="ia-select">
            <option value="">Sin categoría</option>
            @foreach($categorias as $cat)
            <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
            @endforeach
          </select>
        </div>
        <div class="ia-field">
          <label class="ia-lbl">Describe el tema, audiencia y objetivo</label>
          <textarea id="ia-desc-s" class="ia-ta" placeholder="Quiero un artículo sobre cómo atraer clientes con IA en un comercio pequeño..."></textarea>
        </div>
        <button class="ia-btn" id="ia-btn-s" onclick="iaGenerarIdeas()">✦ Generar ideas de títulos</button>
        <div class="ia-hint">La IA sugerirá 5 títulos SEO que puedes añadir al tintero.</div>
        <div class="ia-result" id="ia-ideas-result" style="display:none"></div>
      </div>

      {{-- Modo: serie --}}
      <div id="ia-panel-serie" style="display:none">
        <div class="ia-row">
          <div>
            <label class="ia-lbl">Categoría</label>
            <select id="ia-cat-r" class="ia-select">
              <option value="">Sin categoría</option>
              @foreach($categorias as $cat)
              <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
              @endforeach
            </select>
          </div>
          <div>
            <label class="ia-lbl">Nº artículos</label>
            <select id="ia-n" class="ia-select">
              @foreach([3,4,5,6,7,8,10] as $n)
              <option value="{{ $n }}" {{ $n===5?'selected':'' }}>{{ $n }} artículos</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="ia-field">
          <label class="ia-lbl">Nombre de la serie</label>
          <input type="text" id="ia-nombre-r" class="ia-input" placeholder="IA para comercios locales">
        </div>
        <div class="ia-field">
          <label class="ia-lbl">Describe el tema, audiencia y objetivo</label>
          <textarea id="ia-desc-r" class="ia-ta" placeholder="Quiero atraer más clientes a mi comercio físico usando IA. Audiencia: dueños sin conocimientos técnicos. Guía práctica."></textarea>
        </div>
        <button class="ia-btn" id="ia-btn-r" onclick="iaGenerarPlan()">✦ Generar plan de serie</button>
        <div class="ia-hint">La IA generará los títulos y cómo se enlazan entre sí.</div>
        <div class="ia-result" id="ia-plan-result" style="display:none"></div>
      </div>
    </div>

    {{-- TINTERO --}}
    <div class="tintero-card">
      <div class="tintero-title">
        <span>Tintero</span>
        <span style="font-size:.7rem;color:#9ca3af">{{ $sueltos->count() + $series->sum(fn($s)=>$s->articulos->count()) }} pendientes</span>
      </div>
      <div class="tintero-hint">↔ Artículos sin fecha asignada. Dales fecha desde el editor o con el modal de programación.</div>

      @foreach($series as $serie)
      <div class="tintero-serie">
        <div class="tintero-serie-hdr" onclick="toggleSerie({{ $serie->id }})">
          <span style="font-size:.8rem;color:#9ca3af" id="arrow-{{ $serie->id }}">▶</span>
          <span class="tintero-serie-name">{{ $serie->nombre }}</span>
          <span class="tintero-serie-count">{{ $serie->articulos->count() }} arts.</span>
          <button class="tintero-prog-btn" style="width:auto;margin:0;padding:.2rem .5rem;font-size:.68rem"
                  onclick="event.stopPropagation();abrirModalProgramar({{ $serie->id }}, '{{ addslashes($serie->nombre) }}')">📅</button>
        </div>
        <div class="tintero-serie-body" id="serie-body-{{ $serie->id }}" style="display:none">
          @foreach($serie->articulos as $art)
          @php
            $sinContenido = empty($art->contenido);
            $badgeClass = $sinContenido && $art->estado==='programado' ? 'badge-prog-warn' : ($art->estado==='programado' ? 'badge-prog' : 'badge-bor');
            $badgeLabel = $art->estado === 'programado' ? 'prog' : 'bor';
          @endphp
          <div class="tintero-art">
            <span class="tintero-art-num">{{ $art->orden_en_serie }}.</span>
            <span class="tintero-art-title" title="{{ $art->titulo }}">{{ $art->titulo }}</span>
            <span class="tintero-badge {{ $badgeClass }}">{{ $badgeLabel }}{{ $sinContenido ? ' ⚠' : '' }}</span>
            <a href="{{ route('admin.articulos.edit', $art) }}" title="Editar">✏</a>
          </div>
          @endforeach
        </div>
      </div>
      @endforeach

      @if($sueltos->isNotEmpty())
      <div class="tintero-sueltos-lbl">Sueltos</div>
      @foreach($sueltos as $art)
      <div class="tintero-art">
        <span class="tintero-art-num">—</span>
        <span class="tintero-art-title" title="{{ $art->titulo }}">{{ $art->titulo }}</span>
        <span class="tintero-badge badge-bor">bor</span>
        <a href="{{ route('admin.articulos.edit', $art) }}" title="Editar">✏</a>
      </div>
      @endforeach
      @endif

      @if($series->isEmpty() && $sueltos->isEmpty())
      <p style="font-size:.78rem;color:#9ca3af;text-align:center;padding:.5rem 0">El tintero está vacío. ¡Usa el panel IA para generar ideas!</p>
      @endif
    </div>

  </aside>

  {{-- ═══ MAIN CALENDAR ══════════════════════════════ --}}
  <div class="cal-main">

    {{-- AVISO BAR --}}
    @if($avisos->isNotEmpty())
    <div class="cal-avisos">
      ⚠ Artículos sin contenido con fecha próxima (≤7 días):
      @foreach($avisos as $av)
      <a href="{{ route('admin.articulos.edit', $av) }}">{{ Str::limit($av->titulo, 40) }} ({{ $av->fecha_publicacion->format('d/m H:i') }})</a>
      @endforeach
    </div>
    @endif

    {{-- TOOLBAR --}}
    <div class="cal-toolbar">
      <button class="cal-btn" onclick="navMes(-1)">‹</button>
      <button class="cal-btn" onclick="irHoy()">Hoy</button>
      <button class="cal-btn" onclick="navMes(1)">›</button>
      <h2 id="cal-mes-label"></h2>
    </div>

    {{-- GRID --}}
    <div class="cal-grid-wrap">
      <div class="cal-grid" id="cal-grid"></div>
    </div>
  </div>
</div>

{{-- MODAL PROGRAMAR SERIE --}}
<div class="cal-modal-bg" id="modal-bg" style="display:none" onclick="cerrarModal(event)">
  <div class="cal-modal-box" onclick="event.stopPropagation()">
    <div class="cal-modal-title" id="modal-title">📅 Programar en calendario</div>

    <label class="cal-modal-lbl">Serie</label>
    <input class="cal-modal-input" id="modal-serie-nombre" readonly style="background:#f9fafb">

    <label class="cal-modal-lbl">Fecha del primer artículo</label>
    <input type="date" class="cal-modal-input" id="modal-fecha" oninput="calcPreview()">

    <label class="cal-modal-lbl">Hora de publicación (todos)</label>
    <input type="time" class="cal-modal-input" id="modal-hora" value="09:00" oninput="calcPreview()">

    <label class="cal-modal-lbl">Cadencia</label>
    <div class="cal-cad-options">
      <button class="cal-cad-opt on" onclick="setCad('xdias',this)">Cada X días</button>
      <button class="cal-cad-opt" onclick="setCad('semana',this)">Día de la semana</button>
      <button class="cal-cad-opt" onclick="setCad('xsemanas',this)">Cada X semanas</button>
    </div>

    <div id="cad-xdias">
      <input type="number" class="cal-sub-input" id="cad-dias" value="7" min="1" oninput="calcPreview()" placeholder="Días entre artículos">
    </div>
    <div id="cad-semana" style="display:none">
      <select class="cal-sub-input" id="cad-dow" onchange="calcPreview()">
        <option value="1">Lunes</option><option value="2">Martes</option><option value="3">Miércoles</option>
        <option value="4">Jueves</option><option value="5">Viernes</option><option value="6">Sábado</option>
        <option value="0">Domingo</option>
      </select>
    </div>
    <div id="cad-xsemanas" style="display:none">
      <input type="number" class="cal-sub-input" id="cad-semanas" value="2" min="1" oninput="calcPreview()" placeholder="Semanas entre artículos">
    </div>

    <div class="cal-preview-title">Vista previa de fechas</div>
    <div id="modal-preview"></div>

    <div class="cal-modal-warn">⚠ Los artículos permanecerán como borradores. Genera el contenido y cámbialo a «Programado» antes de la fecha de publicación.</div>

    <div class="cal-modal-actions">
      <button class="cal-modal-cancel" onclick="document.getElementById('modal-bg').style.display='none'">Cancelar</button>
      <button class="cal-modal-ok" id="modal-ok-btn" onclick="confirmarProgramar()">Confirmar</button>
    </div>
  </div>
</div>

{{-- POPOVER --}}
<div class="cal-popover" id="cal-popover" style="display:none">
  <div class="cal-pop-title" id="pop-titulo"></div>
  <div class="cal-pop-meta" id="pop-meta"></div>
  <div class="cal-pop-actions">
    <a class="cal-pop-btn" id="pop-editar" href="#">✏ Editar artículo</a>
    <a class="cal-pop-btn" id="pop-gen-ia" href="#" style="display:none">✦ Generar contenido con IA</a>
    <button class="cal-pop-btn" id="pop-change-fecha" onclick="popChangeFecha()">📅 Cambiar fecha</button>
    <a class="cal-pop-btn" id="pop-ver-blog" href="#" target="_blank" style="display:none">🔗 Ver en blog</a>
    <button class="cal-pop-btn" onclick="document.getElementById('cal-popover').style.display='none'">✕ Cerrar</button>
  </div>
</div>
@endsection

@push('scripts')
<script>
const CSRF   = document.querySelector('meta[name="csrf-token"]').content;
const BASE   = '{{ url("/admin/calendario") }}';
const ARTURL = '{{ url("/admin/articulos") }}';
const BLOGURL= '{{ url("/blog") }}';

let curYear  = new Date().getFullYear();
let curMonth = new Date().getMonth() + 1;
let evCache  = {};
let modalSerieId = null;
let modalArts    = [];
let cadencia     = 'xdias';
let popArtId     = null;

// ── Helpers ─────────────────────────────────────
function req(url, opts = {}) {
    return fetch(url, {
        headers: { 'Content-Type':'application/json', 'Accept':'application/json', 'X-CSRF-TOKEN': CSRF },
        ...opts,
    }).then(r => r.json());
}
function esc(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
function pad(n) { return String(n).padStart(2,'0'); }

// ── Navigation ───────────────────────────────────
function navMes(d) { curMonth += d; if(curMonth>12){curMonth=1;curYear++;} if(curMonth<1){curMonth=12;curYear--;} loadMonth(); }
function irHoy()   { const t=new Date(); curYear=t.getFullYear(); curMonth=t.getMonth()+1; loadMonth(); }

async function loadMonth() {
    const key = `${curYear}-${curMonth}`;
    if (!evCache[key]) {
        const data = await req(`${BASE}/events?year=${curYear}&month=${curMonth}`);
        evCache[key] = Array.isArray(data) ? data : [];
    }
    render(curYear, curMonth, evCache[key]);
}

// ── Render calendar ──────────────────────────────
function render(y, m, evs) {
    const label = new Date(y, m-1, 1).toLocaleString('es-ES', {month:'long', year:'numeric'});
    document.getElementById('cal-mes-label').textContent = label.charAt(0).toUpperCase() + label.slice(1);

    const daysInMonth = new Date(y, m, 0).getDate();
    const firstDow    = new Date(y, m-1, 1).getDay();
    const todayStr    = new Date().toISOString().slice(0,10);

    const byDate = {};
    evs.forEach(e => { const d = e.fecha_publicacion.split(' ')[0]; (byDate[d] = byDate[d]||[]).push(e); });

    let html = ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'].map(d => `<div class="cal-day-hdr">${d}</div>`).join('');
    for (let i = 0; i < firstDow; i++) html += `<div class="cal-cell cal-cell--empty"></div>`;

    for (let day = 1; day <= daysInMonth; day++) {
        const ds   = `${y}-${pad(m)}-${pad(day)}`;
        const arts = byDate[ds] || [];
        const today = ds === todayStr ? ' cal-cell--today' : '';
        html += `<div class="cal-cell${today}"><span class="cal-day-num">${day}</span><div class="cal-events">`;
        arts.forEach(e => {
            const cls = e.estado==='publicado' ? 'cal-ev--pub'
                      : (e.contenido_vacio && e.estado==='programado') ? 'cal-ev--warn'
                      : e.estado==='programado' ? 'cal-ev--prog' : 'cal-ev--bor';
            const hora = e.fecha_publicacion.split(' ')[1]?.slice(0,5) || '';
            const warn = e.contenido_vacio ? '⚠' : '';
            html += `<div class="cal-ev ${cls}" onclick="abrirPopover(${e.id}, event)">
                <span class="cal-ev-title">${esc(e.titulo)}</span>
                <span class="cal-ev-time">${hora}${warn}</span>
            </div>`;
        });
        html += `</div></div>`;
    }
    document.getElementById('cal-grid').innerHTML = html;
}

// ── Popover ──────────────────────────────────────
function abrirPopover(id, ev) {
    ev.stopPropagation();
    const key = `${curYear}-${curMonth}`;
    const art = evCache[key]?.find(a => a.id === id);
    if (!art) return;
    popArtId = id;
    const slug = art.slug;
    const hora = art.fecha_publicacion.split(' ')[1]?.slice(0,5) || '';
    const fecha= art.fecha_publicacion.split(' ')[0];
    document.getElementById('pop-titulo').textContent = art.titulo;
    document.getElementById('pop-meta').textContent   = `${fecha} ${hora} — ${art.estado}`;
    document.getElementById('pop-editar').href   = `${ARTURL}/${id}/edit`;
    const genIa = document.getElementById('pop-gen-ia');
    genIa.style.display = art.contenido_vacio ? 'block' : 'none';
    genIa.href = `${ARTURL}/${id}/edit`;
    const verBlog = document.getElementById('pop-ver-blog');
    verBlog.style.display = art.estado === 'publicado' ? 'block' : 'none';
    verBlog.href = `${BLOGURL}/${slug}`;
    const pop = document.getElementById('cal-popover');
    pop.style.display = 'block';
    const r = ev.target.getBoundingClientRect();
    pop.style.left = Math.min(r.left, window.innerWidth - 280) + 'px';
    pop.style.top  = (r.bottom + 4) + 'px';
}

document.addEventListener('click', e => {
    const pop = document.getElementById('cal-popover');
    if (pop.style.display !== 'none' && !pop.contains(e.target)) pop.style.display = 'none';
});

function popChangeFecha() {
    if (!popArtId) return;
    window.location.href = `${ARTURL}/${popArtId}/edit`;
}

// ── Tintero acordeón ─────────────────────────────
function toggleSerie(id) {
    const body  = document.getElementById(`serie-body-${id}`);
    const arrow = document.getElementById(`arrow-${id}`);
    const open  = body.style.display !== 'none';
    body.style.display  = open ? 'none' : 'block';
    arrow.textContent   = open ? '▶' : '▼';
}

// ── Modal programar ──────────────────────────────
async function abrirModalProgramar(serieId, nombre) {
    modalSerieId = serieId;
    document.getElementById('modal-serie-nombre').value = nombre;
    modalArts = window.TINTERO_SERIES[serieId] || [];
    calcPreview();
    document.getElementById('modal-bg').style.display = 'flex';
}

function cerrarModal(e) {
    if (e.target === document.getElementById('modal-bg')) {
        document.getElementById('modal-bg').style.display = 'none';
    }
}

function setCad(c, btn) {
    cadencia = c;
    document.querySelectorAll('.cal-cad-opt').forEach(b => b.classList.remove('on'));
    btn.classList.add('on');
    ['xdias','semana','xsemanas'].forEach(k => {
        document.getElementById(`cad-${k}`).style.display = k === c ? 'block' : 'none';
    });
    calcPreview();
}

function calcPreview() {
    const fechaStr = document.getElementById('modal-fecha').value;
    const hora     = document.getElementById('modal-hora').value || '09:00';
    if (!fechaStr) { document.getElementById('modal-preview').innerHTML = '<p style="font-size:.75rem;color:#9ca3af">Elige una fecha para ver la vista previa.</p>'; return; }

    let base = new Date(`${fechaStr}T${hora}`);
    const arts = modalArts;
    let html = '';

    arts.forEach((art, i) => {
        let d = new Date(base);
        if (i > 0) {
            if (cadencia === 'xdias') {
                const dias = parseInt(document.getElementById('cad-dias').value) || 7;
                d = new Date(base.getTime() + i * dias * 86400000);
            } else if (cadencia === 'semana') {
                const dow = parseInt(document.getElementById('cad-dow').value);
                let cur = new Date(base);
                let count = 0;
                while (count < i) { cur.setDate(cur.getDate()+1); if(cur.getDay()===dow) count++; }
                cur.setHours(base.getHours(), base.getMinutes());
                d = cur;
            } else {
                const sems = parseInt(document.getElementById('cad-semanas').value) || 2;
                d = new Date(base.getTime() + i * sems * 7 * 86400000);
            }
        }
        const dateLabel = d.toLocaleDateString('es-ES',{weekday:'short',day:'2-digit',month:'short'});
        const timeLabel = d.toTimeString().slice(0,5);
        const warn = art.sin_contenido ? '<span class="warn">⚠ sin contenido</span>' : '';
        html += `<div class="cal-preview-item"><span>${i+1}. ${esc(art.titulo)}</span><span>${dateLabel} ${timeLabel} ${warn}</span></div>`;
    });
    document.getElementById('modal-preview').innerHTML = html || '<p style="font-size:.75rem;color:#9ca3af">No hay artículos en el tintero para esta serie.</p>';
}

async function confirmarProgramar() {
    const fecha = document.getElementById('modal-fecha').value;
    const hora  = document.getElementById('modal-hora').value || '09:00';
    if (!fecha) { alert('Elige una fecha de inicio.'); return; }

    const btn = document.getElementById('modal-ok-btn');
    btn.disabled = true; btn.textContent = 'Guardando...';

    const payload = {
        serie_id:       modalSerieId,
        start_datetime: `${fecha} ${hora}`,
        cadencia,
        cada_x_dias:    parseInt(document.getElementById('cad-dias').value) || 7,
        dia_semana:     parseInt(document.getElementById('cad-dow').value),
        cada_x_semanas: parseInt(document.getElementById('cad-semanas').value) || 2,
    };

    try {
        const res = await req(`${BASE}/programar`, { method:'POST', body: JSON.stringify(payload) });
        if (res.ok) {
            document.getElementById('modal-bg').style.display = 'none';
            evCache = {};
            loadMonth();
            location.reload();
        } else {
            alert('Error: ' + (res.message || 'Desconocido'));
        }
    } finally {
        btn.disabled = false; btn.textContent = 'Confirmar';
    }
}

// ── IA Panel ─────────────────────────────────────
function setIaMode(mode) {
    document.getElementById('ia-panel-single').style.display = mode==='single' ? 'block' : 'none';
    document.getElementById('ia-panel-serie').style.display  = mode==='serie'  ? 'block' : 'none';
    document.getElementById('ia-mode-single').classList.toggle('on', mode==='single');
    document.getElementById('ia-mode-serie').classList.toggle('on', mode==='serie');
}

async function iaGenerarIdeas() {
    const desc = document.getElementById('ia-desc-s').value.trim();
    if (!desc) { alert('Escribe la descripción primero.'); return; }
    const btn = document.getElementById('ia-btn-s');
    btn.disabled = true; btn.textContent = '✦ Generando...';
    const catId = document.getElementById('ia-cat-s').value;
    try {
        const res = await req(`${BASE}/ia/ideas`, { method:'POST', body: JSON.stringify({ descripcion: desc, categoria_blog_id: catId||null }) });
        if (!res.ok) { alert('Error: ' + res.message); return; }
        const box = document.getElementById('ia-ideas-result');
        box.style.display = 'block';
        box.innerHTML = '<div style="font-size:.75rem;font-weight:700;color:#374151;margin-bottom:.4rem">Ideas generadas:</div>' +
            (res.ideas||[]).map((t,i) => `<div class="ia-idea-item">
                <button class="ia-idea-add" onclick="addIdeaToTintero(${JSON.stringify(esc(t))}, ${catId||'null'})" title="Añadir al tintero">+</button>
                <span>${esc(t)}</span>
            </div>`).join('');
    } finally {
        btn.disabled = false; btn.textContent = '✦ Generar ideas de títulos';
    }
}

async function addIdeaToTintero(titulo, catId) {
    const res = await req(`${BASE}/tintero/articulo`, { method:'POST', body: JSON.stringify({ titulo, categoria_blog_id: catId }) });
    if (res.ok) { alert('Añadido al tintero. Recarga para verlo.'); }
}

async function iaGenerarPlan() {
    const nombre = document.getElementById('ia-nombre-r').value.trim();
    const desc   = document.getElementById('ia-desc-r').value.trim();
    const n      = parseInt(document.getElementById('ia-n').value);
    if (!nombre || !desc) { alert('Rellena el nombre y la descripción.'); return; }
    const btn = document.getElementById('ia-btn-r');
    btn.disabled = true; btn.textContent = '✦ Generando plan...';
    try {
        const res = await req(`${BASE}/ia/plan`, { method:'POST', body: JSON.stringify({ nombre, descripcion: desc, n_articulos: n }) });
        if (!res.ok) { alert('Error: ' + res.message); return; }
        const box = document.getElementById('ia-plan-result');
        box.style.display = 'block';
        const plan = res.plan || [];
        box.innerHTML = '<div style="font-size:.75rem;font-weight:700;color:#374151;margin-bottom:.4rem">Plan generado:</div>' +
            plan.map(p => `<div class="ia-plan-item">
                <div class="ia-plan-orden">Parte ${p.orden}</div>
                <div class="ia-plan-titulo">${esc(p.titulo)}</div>
                <div class="ia-plan-desc">${esc(p.descripcion||'')}</div>
            </div>`).join('') +
            `<div class="ia-plan-actions">
                <button class="ia-plan-btn ia-plan-add" onclick="addPlanToTintero(${JSON.stringify(JSON.stringify(plan))}, '${esc(nombre)}')">+ Añadir al tintero</button>
                <button class="ia-plan-btn ia-plan-prog" onclick="addPlanAndProgram(${JSON.stringify(JSON.stringify(plan))}, '${esc(nombre)}')">📅 Programar en calendario</button>
            </div>`;
    } finally {
        btn.disabled = false; btn.textContent = '✦ Generar plan de serie';
    }
}

async function addPlanToTintero(planJson, nombre) {
    const plan   = JSON.parse(planJson);
    const catId  = document.getElementById('ia-cat-r').value || null;
    const res = await req(`${BASE}/tintero/serie`, { method:'POST', body: JSON.stringify({
        nombre, categoria_blog_id: catId,
        articulos: plan.map(p => ({ titulo: p.titulo })),
    })});
    if (res.ok) { alert('Serie y artículos añadidos al tintero. Recargando...'); location.reload(); }
    else alert('Error: ' + res.message);
}

async function addPlanAndProgram(planJson, nombre) {
    const plan   = JSON.parse(planJson);
    const catId  = document.getElementById('ia-cat-r').value || null;
    const res = await req(`${BASE}/tintero/serie`, { method:'POST', body: JSON.stringify({
        nombre, categoria_blog_id: catId,
        articulos: plan.map(p => ({ titulo: p.titulo })),
    })});
    if (res.ok) {
        modalArts    = res.articulos.map(a => ({ titulo: a.titulo, sin_contenido: true }));
        modalSerieId = res.serie.id;
        document.getElementById('modal-serie-nombre').value = nombre;
        calcPreview();
        document.getElementById('modal-bg').style.display = 'flex';
    } else { alert('Error: ' + res.message); }
}

// ── Tintero series data for modal ────────────────
window.TINTERO_SERIES = {
    @foreach($series as $serie)
    {{ $serie->id }}: {!! json_encode($serie->articulos->map(fn($a) => ['id' => $a->id, 'titulo' => $a->titulo, 'sin_contenido' => empty($a->contenido)])->values()) !!},
    @endforeach
};

// ── Init ─────────────────────────────────────────
loadMonth();
</script>
@endpush
