@php
    $articleId  = isset($articulo) ? $articulo->id : null;
    $yaGenerado = isset($articulo) && $articulo->ai_generated;
@endphp

<div id="ai-panel" style="background:linear-gradient(135deg,#f5f3ff,#ede9fe);border:1.5px solid #c4b5fd;border-radius:12px;margin-bottom:1.25rem">
    {{-- Cabecera con toggle --}}
    <div style="display:flex;align-items:center;gap:.5rem;padding:1rem 1.5rem;cursor:pointer"
         onclick="toggleAiPanel()">
        <span style="font-size:1.1rem">✦</span>
        <h3 style="font-size:1rem;font-weight:700;color:#5b21b6;margin:0;flex:1">Asistente IA</h3>
        @if($yaGenerado)
        <span style="font-size:.72rem;color:#7c3aed;background:#ddd6fe;padding:.15rem .5rem;border-radius:20px">ya generado</span>
        @endif
        <span style="font-size:.75rem;color:#7c3aed;background:#ede9fe;padding:.15rem .5rem;border-radius:20px">beta</span>
        <span id="ai-panel-chevron" style="color:#7c3aed;font-size:.8rem;transition:transform .2s">▼</span>
    </div>

    {{-- Cuerpo colapsable --}}
    <div id="ai-panel-body" style="padding:0 1.5rem 1.5rem">
        <div class="form-group">
            <label class="form-label" style="color:#5b21b6;font-weight:600">Idea principal *</label>
            <textarea id="ai-idea" class="form-control" rows="3"
                placeholder="Ej: Cómo un bar puede conseguir clientes recurrentes usando QR y enviando promociones a los que ya le visitaron..."></textarea>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label" style="color:#5b21b6">Keyword principal</label>
                <input type="text" id="ai-keyword" class="form-control" placeholder="fidelización clientes bar">
            </div>
            <div class="form-group">
                <label class="form-label" style="color:#5b21b6">Localidad (opcional)</label>
                <input type="text" id="ai-localidad" class="form-control" placeholder="Madrid, Coslada...">
            </div>
            <div class="form-group">
                <label class="form-label" style="color:#5b21b6">Tono</label>
                <select id="ai-tono" class="form-control">
                    <option value="profesional y cercano">Profesional y cercano</option>
                    <option value="informativo y directo">Informativo y directo</option>
                    <option value="divulgativo y ameno">Divulgativo y ameno</option>
                    <option value="comercial y persuasivo">Comercial y persuasivo</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" style="color:#5b21b6">Instrucciones adicionales</label>
            <input type="text" id="ai-instrucciones" class="form-control"
                placeholder="Ej: menciona que Eventify no requiere app, enfócate en bares pequeños...">
        </div>

        <div style="display:flex;gap:1.5rem;flex-wrap:wrap;margin-bottom:1.25rem">
            <label style="display:flex;align-items:center;gap:.4rem;font-size:.85rem;cursor:pointer;color:#5b21b6">
                <input type="checkbox" id="ai-faq" checked> Generar FAQ
            </label>
            <label style="display:flex;align-items:center;gap:.4rem;font-size:.85rem;cursor:pointer;color:#5b21b6">
                <input type="checkbox" id="ai-links" checked> Sugerir enlaces internos
            </label>
            <label style="display:flex;align-items:center;gap:.4rem;font-size:.85rem;cursor:pointer;color:#5b21b6">
                <input type="checkbox" id="ai-img"> Generar imagen con IA
                <small style="color:#9ca3af">(coste extra)</small>
            </label>
        </div>

        <div style="display:flex;gap:.75rem;flex-wrap:wrap;align-items:center">
            <button type="button" id="ai-generate-btn"
                style="background:#6c3fc5;color:#fff;border:none;border-radius:8px;padding:.6rem 1.25rem;font-size:.9rem;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:.5rem">
                ✦ Generar con IA
            </button>
            <span id="ai-status" style="font-size:.85rem;color:#7c3aed;display:none">
                <span id="ai-spinner" style="animation:spin 1s linear infinite;display:inline-block">⟳</span>
                <span id="ai-status-text">Generando...</span>
            </span>
            <span id="ai-error" style="font-size:.85rem;color:#ef4444;display:none"></span>
        </div>

        {{-- Sugerencias de enlaces internos --}}
        <div id="ai-links-suggestions" style="display:none;margin-top:1.25rem;background:#fff;border:1px solid #e5e7eb;border-radius:8px;padding:1rem">
            <p style="font-size:.85rem;font-weight:600;color:#374151;margin-bottom:.75rem">🔗 Sugerencias de enlaces internos:</p>
            <div id="ai-links-list"></div>
            <p style="font-size:.75rem;color:#9ca3af;margin-top:.5rem">
                Los marcados en verde ya se han insertado en el contenido.
            </p>
        </div>
    </div>
</div>

{{-- Overlay bloqueante mientras la IA genera --}}
<div id="ai-gen-overlay" style="position:fixed;inset:0;background:rgba(15,15,30,.65);z-index:9990;display:none;align-items:center;justify-content:center">
    <div style="background:#fff;border-radius:16px;padding:2rem 2.25rem;max-width:400px;width:92%;text-align:center;box-shadow:0 24px 64px rgba(0,0,0,.35)">
        <div id="ai-ov-icon" style="font-size:2rem;margin-bottom:.6rem;display:inline-block;color:#6c3fc5">✦</div>
        <h3 id="ai-ov-title" style="margin:0 0 .4rem;font-size:1rem;font-weight:700;color:#1f2937">Generando artículo con IA</h3>
        <p id="ai-ov-msg" style="font-size:.85rem;color:#6b7280;margin:0 0 1.25rem;min-height:2.5rem"></p>
        <div id="ai-ov-items" style="display:none;background:#f9fafb;border-radius:8px;padding:.75rem 1rem;text-align:left;margin-bottom:1rem;font-size:.82rem;color:#374151;line-height:1.7"></div>
        <div id="ai-ov-warn" style="background:#fef3c7;border:1px solid #fcd34d;border-radius:8px;padding:.55rem .85rem;font-size:.76rem;color:#92400e">
            ⚠ No cierres ni recargues esta página
        </div>
        <button type="button" id="ai-ov-close"
            onclick="document.getElementById('ai-gen-overlay').style.display='none'"
            style="display:none;margin-top:1rem;background:#6c3fc5;color:#fff;border:none;border-radius:8px;padding:.55rem 1.5rem;font-size:.88rem;font-weight:600;cursor:pointer;width:100%">
            Cerrar
        </button>
    </div>
</div>

@push('scripts')
<script>
const AI_ARTICLE_ID  = {{ $articleId ?? 'null' }};
const AI_YA_GENERADO = {{ $yaGenerado ? 'true' : 'false' }};
const AI_LS_KEY      = `ai_panel_${AI_ARTICLE_ID || 'new'}`;

// ── Colapsar / expandir panel ──
function toggleAiPanel(force) {
    const body    = document.getElementById('ai-panel-body');
    const chevron = document.getElementById('ai-panel-chevron');
    const hidden  = body.style.display === 'none';
    const show    = force !== undefined ? force : hidden;
    body.style.display       = show ? 'block' : 'none';
    chevron.style.transform  = show ? 'rotate(0deg)' : 'rotate(-90deg)';
    try { localStorage.setItem(AI_LS_KEY + '_open', show ? '1' : '0'); } catch(e) {}
}

// ── Persistir campos en localStorage ──
const AI_FIELDS_SAVE = ['ai-idea','ai-keyword','ai-localidad','ai-instrucciones'];
const AI_CHECKS_SAVE = ['ai-faq','ai-links','ai-img'];

function aiSaveFields() {
    const data = {};
    AI_FIELDS_SAVE.forEach(id => { const el = document.getElementById(id); if (el) data[id] = el.value; });
    AI_CHECKS_SAVE.forEach(id => { const el = document.getElementById(id); if (el) data[id] = el.checked; });
    const sel = document.getElementById('ai-tono'); if (sel) data['ai-tono'] = sel.value;
    try { localStorage.setItem(AI_LS_KEY + '_fields', JSON.stringify(data)); } catch(e) {}
}

function aiLoadFields() {
    try {
        const raw = localStorage.getItem(AI_LS_KEY + '_fields');
        if (!raw) return;
        const data = JSON.parse(raw);
        AI_FIELDS_SAVE.forEach(id => { const el = document.getElementById(id); if (el && data[id]) el.value = data[id]; });
        AI_CHECKS_SAVE.forEach(id => { const el = document.getElementById(id); if (el && data[id] !== undefined) el.checked = !!data[id]; });
        const sel = document.getElementById('ai-tono'); if (sel && data['ai-tono']) sel.value = data['ai-tono'];
    } catch(e) {}
}

// Inicializar panel al cargar
(function initAiPanel() {
    // Estado colapso: si ya fue generado colapsar por defecto; localStorage sobreescribe
    try {
        const stored = localStorage.getItem(AI_LS_KEY + '_open');
        const open   = stored !== null ? stored === '1' : !AI_YA_GENERADO;
        toggleAiPanel(open);
    } catch(e) {
        toggleAiPanel(!AI_YA_GENERADO);
    }
    // Cargar campos guardados
    aiLoadFields();
    // Guardar en cada cambio
    [...AI_FIELDS_SAVE, ...AI_CHECKS_SAVE, 'ai-tono'].forEach(id => {
        const el = document.getElementById(id);
        if (!el) return;
        el.addEventListener('input',  aiSaveFields);
        el.addEventListener('change', aiSaveFields);
    });
})();
const AI_GENERATE_URL   = '{{ route("admin.ia.generate") }}';
const AI_ADMIN_BASE     = '{{ url("/admin") }}';
const AI_REGENERATE_URL = AI_ARTICLE_ID ? `${AI_ADMIN_BASE}/articulos/${AI_ARTICLE_ID}/ai/regenerate` : null;
const AI_IMAGE_URL      = AI_ARTICLE_ID ? `${AI_ADMIN_BASE}/articulos/${AI_ARTICLE_ID}/ai/image` : null;
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';

function aiSetStatus(msg, show = true) {
    document.getElementById('ai-status').style.display = show ? 'inline-flex' : 'none';
    document.getElementById('ai-status-text').textContent = msg;
    document.getElementById('ai-error').style.display = 'none';
}
function aiSetError(msg) {
    document.getElementById('ai-status').style.display = 'none';
    const err = document.getElementById('ai-error');
    err.textContent = '✗ ' + msg;
    err.style.display = 'inline';
}
function aiSetField(name, value) {
    const el = document.querySelector(`[name="${name}"]`);
    if (!el) return;
    el.value = (typeof value === 'object') ? JSON.stringify(value, null, 2) : (value ?? '');
    el.dispatchEvent(new Event('input'));
}
async function aiPost(url, data) {
    const r = await fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': CSRF,
        },
        body: JSON.stringify(data),
    });
    const ct = r.headers.get('content-type') || '';
    if (!ct.includes('json')) {
        throw new Error(`Error del servidor (HTTP ${r.status}). Recarga la página e inicia sesión si es necesario.`);
    }
    return r.json();
}

// ── Overlay de generación ──
let _aiOvTimer = null;

function aiOvShow() {
    clearInterval(_aiOvTimer);
    const ov = document.getElementById('ai-gen-overlay');
    document.getElementById('ai-ov-icon').textContent       = '✦';
    document.getElementById('ai-ov-icon').style.color       = '#6c3fc5';
    document.getElementById('ai-ov-icon').style.animation   = 'spin 2s linear infinite';
    document.getElementById('ai-ov-title').textContent      = 'Generando artículo con IA';
    document.getElementById('ai-ov-items').style.display    = 'none';
    document.getElementById('ai-ov-items').innerHTML        = '';
    document.getElementById('ai-ov-close').style.display    = 'none';
    document.getElementById('ai-ov-warn').style.display     = 'block';
    ov.style.display = 'flex';

    const msgs = [
        'Analizando idea y keyword principal...',
        'Redactando contenido y subtítulos...',
        'Generando metadatos SEO y resúmenes...',
        'Añadiendo FAQ y sugerencias de enlaces...',
        'Casi listo...',
    ];
    let i = 0;
    document.getElementById('ai-ov-msg').textContent = msgs[0];
    _aiOvTimer = setInterval(() => {
        i = Math.min(i + 1, msgs.length - 1);
        document.getElementById('ai-ov-msg').textContent = msgs[i];
    }, 9000);
}

function aiOvDone(titulo, extraMsg, autoClose) {
    clearInterval(_aiOvTimer);
    document.getElementById('ai-ov-icon').style.animation  = 'none';
    document.getElementById('ai-ov-icon').textContent      = '✓';
    document.getElementById('ai-ov-icon').style.color      = '#059669';
    document.getElementById('ai-ov-title').textContent     = '¡Artículo generado!';
    document.getElementById('ai-ov-msg').textContent       = extraMsg || 'Revisa y guarda los cambios.';
    document.getElementById('ai-ov-warn').style.display    = 'none';
    if (titulo) {
        const items = document.getElementById('ai-ov-items');
        items.innerHTML     = `<strong>Título:</strong> ${titulo}`;
        items.style.display = 'block';
    }
    if (!autoClose) document.getElementById('ai-ov-close').style.display = 'block';
}

function aiOvError(msg) {
    clearInterval(_aiOvTimer);
    document.getElementById('ai-ov-icon').style.animation  = 'none';
    document.getElementById('ai-ov-icon').textContent      = '✗';
    document.getElementById('ai-ov-icon').style.color      = '#dc2626';
    document.getElementById('ai-ov-title').textContent     = 'Error al generar';
    document.getElementById('ai-ov-msg').textContent       = msg;
    document.getElementById('ai-ov-warn').style.display    = 'none';
    document.getElementById('ai-ov-close').style.display   = 'block';
}

// Generar artículo completo
document.getElementById('ai-generate-btn').addEventListener('click', async () => {
    const idea = document.getElementById('ai-idea').value.trim();
    if (!idea) { alert('Escribe la idea principal primero.'); return; }

    document.getElementById('ai-generate-btn').disabled = true;
    aiOvShow();

    const payload = {
        idea,
        focus_keyword:  document.getElementById('ai-keyword').value,
        localidad:      document.getElementById('ai-localidad').value,
        tono:           document.getElementById('ai-tono').value,
        instrucciones:  document.getElementById('ai-instrucciones').value,
        categoria_id:   parseInt(document.getElementById('categoria_blog_id')?.value) || null,
        generate_image: document.getElementById('ai-img').checked,
        generate_faq:   document.getElementById('ai-faq').checked,
        suggest_links:  document.getElementById('ai-links').checked,
        serie_id:       parseInt(document.getElementById('serie_id')?.value) || null,
        orden_en_serie: parseInt(document.getElementById('orden_en_serie')?.value) || null,
    };

    try {
        const res = await aiPost(AI_GENERATE_URL, payload);
        if (!res.ok) { aiOvError(res.message || 'Error desconocido'); return; }

        const d = res.data;

        // Artículo nuevo: guardado automático → redirigir al editor
        if (!AI_ARTICLE_ID && res.edit_url) {
            aiOvDone(d.titulo, 'Borrador guardado. Abriendo editor...', true);
            setTimeout(() => { window.location.href = res.edit_url; }, 1800);
            return;
        }

        // Artículo existente: rellenar campos del formulario
        aiSetField('titulo',             d.titulo);
        aiSetField('slug',               d.slug);
        aiSetField('extracto',           d.extracto);
        aiSetField('focus_keyword',      d.focus_keyword);
        aiSetField('etiquetas',          d.etiquetas);
        aiSetField('meta_title',         d.meta_title);
        aiSetField('meta_description',   d.meta_description);
        aiSetField('image_alt',          d.image_alt);
        aiSetField('ai_context_summary', d.ai_context_summary);
        aiSetField('summary_short',      d.summary_short);

        if (d.contenido) {
            aiSetField('contenido', d.contenido);
            if (window.quillEditor) {
                const html = typeof marked !== 'undefined' ? marked.parse(d.contenido) : d.contenido;
                window.quillEditor.clipboard.dangerouslyPasteHTML(html);
            }
        }

        if (d.faq_json) aiSetField('faq_json', d.faq_json);
        if (d.imagen_principal) {
            aiSetField('imagen_principal', d.imagen_principal);
            if (typeof actualizarPreviewImagen === 'function') actualizarPreviewImagen(d.imagen_principal);
        }

        const schemaEl = document.querySelector('[name="schema_type"]');
        if (schemaEl && d.schema_type) schemaEl.value = d.schema_type;

        if (d.internal_links_suggested?.length) renderLinkSuggestions(d.internal_links_suggested);

        const extra = d.image_error ? `Imagen falló: ${d.image_error}` : 'Revisa y guarda los cambios.';
        aiOvDone(d.titulo, extra);
        aiSetStatus('✓ Artículo generado. Revisa y guarda.', true);
        document.getElementById('ai-spinner').style.display = 'none';

    } catch (e) {
        aiOvError('Error de conexión: ' + e.message);
    } finally {
        document.getElementById('ai-generate-btn').disabled = false;
    }
});

// Solo imagen (artículo existente)
document.getElementById('ai-image-btn')?.addEventListener('click', async () => {
    if (!AI_IMAGE_URL) return;
    const btn      = document.getElementById('ai-image-btn');
    const statusEl = document.getElementById('img-ai-status');
    const statusTx = document.getElementById('img-ai-status-text');

    // Construir prompt desde los datos del artículo:
    // alt-text (describe exactamente qué mostrar) + título + extracto como contexto
    const titulo = document.querySelector('[name="titulo"]')?.value.trim() || '';
    const alt    = document.getElementById('image_alt')?.value.trim() || '';
    const extr   = document.querySelector('[name="extracto"]')?.value.trim() || '';

    let prompt;
    if (alt) {
        // Si hay alt text, úsalo como descripción principal + título de contexto
        prompt = alt + (titulo ? '. Contexto: ' + titulo : '');
    } else {
        // Sin alt: título + extracto (primeros 200 chars)
        prompt = titulo + (extr ? '. ' + extr.slice(0, 200) : '');
    }

    btn.disabled      = true;
    btn.style.opacity = '0.55';
    if (statusEl) { statusEl.style.display = 'flex'; statusEl.style.color = '#7c3aed'; }
    if (statusTx) statusTx.textContent = 'Generando imagen... (puede tardar 30-60 s)';

    try {
        const res = await aiPost(AI_IMAGE_URL, { prompt });
        if (res.ok) {
            aiSetField('imagen_principal', res.url);
            if (typeof actualizarPreviewImagen === 'function') actualizarPreviewImagen(res.url);
            if (statusEl) statusEl.style.color = '#059669';
            if (statusTx) statusTx.textContent = '✓ Imagen generada.';
            setTimeout(() => { if (statusEl) statusEl.style.display = 'none'; }, 4000);
        } else {
            if (statusEl) statusEl.style.color = '#dc2626';
            if (statusTx) statusTx.textContent = '✗ ' + (res.message || 'Error al generar imagen');
        }
    } catch (e) {
        if (statusEl) statusEl.style.color = '#dc2626';
        if (statusTx) statusTx.textContent = '✗ Error de conexión: ' + e.message;
    } finally {
        btn.disabled      = false;
        btn.style.opacity = '1';
    }
});

// Regenerar campo individual (botones ✦ en _form)
document.addEventListener('click', async (e) => {
    const btn = e.target.closest('.btn-regen');
    if (!btn) return;
    const field = btn.dataset.field;
    const url   = AI_REGENERATE_URL || AI_GENERATE_URL;
    if (!url) return;

    btn.classList.add('loading');
    btn.textContent = '⟳';

    const context = {
        titulo:        document.querySelector('[name="titulo"]')?.value,
        extracto:      document.querySelector('[name="extracto"]')?.value,
        contenido:     window.quillEditor ? window.quillEditor.getText() : document.querySelector('[name="contenido"]')?.value,
        focus_keyword: document.querySelector('[name="focus_keyword"]')?.value,
    };

    try {
        let res;
        if (AI_REGENERATE_URL) {
            res = await aiPost(AI_REGENERATE_URL, { field, context });
        } else {
            // Artículo nuevo: simulamos regeneración pasando idea + campo
            res = await aiPost(AI_GENERATE_URL, { idea: context.titulo || context.contenido?.slice(0, 200), field });
        }
        if (res.ok) {
            aiSetField(field, AI_REGENERATE_URL ? res.value : (res.data?.[field] ?? ''));
        } else {
            alert('Error: ' + res.message);
        }
    } catch (err) {
        alert('Error: ' + err.message);
    } finally {
        btn.classList.remove('loading');
        btn.textContent = '✦';
    }
});

function renderLinkSuggestions(links) {
    const container = document.getElementById('ai-links-suggestions');
    const list      = document.getElementById('ai-links-list');
    list.innerHTML  = links.map(l => `
        <div style="display:flex;align-items:flex-start;gap:.5rem;margin-bottom:.5rem;font-size:.85rem">
            <span style="color:#10b981;font-size:1rem">↗</span>
            <div>
                <strong>${l.titulo}</strong> — anchor: "<em>${l.anchor}</em>"<br>
                <span style="color:#9ca3af">${l.razon}</span>
            </div>
        </div>
    `).join('');
    container.style.display = 'block';
}
</script>
@endpush
