@php $articleId = isset($articulo) ? $articulo->id : null; @endphp

<div id="ai-panel" style="background:linear-gradient(135deg,#f5f3ff,#ede9fe);border:1.5px solid #c4b5fd;border-radius:12px;padding:1.5rem;margin-bottom:2rem">
    <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:1.25rem">
        <span style="font-size:1.1rem">✦</span>
        <h3 style="font-size:1rem;font-weight:700;color:#5b21b6;margin:0">Asistente IA</h3>
        <span style="font-size:.75rem;color:#7c3aed;background:#ede9fe;padding:.15rem .5rem;border-radius:20px;margin-left:.5rem">beta</span>
    </div>

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
        @if($articleId)
        <button type="button" id="ai-image-btn"
            style="background:#fff;color:#6c3fc5;border:1.5px solid #c4b5fd;border-radius:8px;padding:.55rem 1rem;font-size:.85rem;cursor:pointer">
            🖼 Solo imagen
        </button>
        @endif
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
            Los marcados en verde ya se han insertado en el contenido. Puedes editar el Markdown manualmente.
        </p>
    </div>
</div>

@push('head')
<style>
@keyframes spin { to { transform: rotate(360deg); } }
.btn-regen {
    display: inline-flex; align-items: center; justify-content: center;
    width: 20px; height: 20px; border-radius: 50%;
    background: #ede9fe; color: #7c3aed; border: none;
    font-size: 11px; cursor: pointer; margin-left: 5px;
    vertical-align: middle; transition: background .15s;
    flex-shrink: 0;
}
.btn-regen:hover { background: #c4b5fd; }
.btn-regen.loading { animation: spin .7s linear infinite; pointer-events: none; }
</style>
@endpush

@push('scripts')
<script>
const AI_ARTICLE_ID = {{ $articleId ?? 'null' }};
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

// Generar artículo completo
document.getElementById('ai-generate-btn').addEventListener('click', async () => {
    const idea = document.getElementById('ai-idea').value.trim();
    if (!idea) { alert('Escribe la idea principal primero.'); return; }

    document.getElementById('ai-generate-btn').disabled = true;
    aiSetStatus('Generando artículo... (puede tardar 20-40 segundos)');

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
    };

    try {
        const res = await aiPost(AI_GENERATE_URL, payload);
        if (!res.ok) { aiSetError(res.message || 'Error desconocido'); return; }

        const d = res.data;
        aiSetField('titulo',             d.titulo);
        aiSetField('slug',               d.slug);
        aiSetField('extracto',           d.extracto);
        aiSetField('contenido',          d.contenido);
        aiSetField('focus_keyword',      d.focus_keyword);
        aiSetField('etiquetas',          d.etiquetas);
        aiSetField('meta_title',         d.meta_title);
        aiSetField('meta_description',   d.meta_description);
        aiSetField('image_alt',          d.image_alt);
        aiSetField('ai_context_summary', d.ai_context_summary);
        aiSetField('summary_short',      d.summary_short);

        if (d.faq_json) aiSetField('faq_json', d.faq_json);
        if (d.imagen_principal) aiSetField('imagen_principal', d.imagen_principal);

        const schemaEl = document.querySelector('[name="schema_type"]');
        if (schemaEl && d.schema_type) schemaEl.value = d.schema_type;

        if (d.internal_links_suggested?.length) {
            renderLinkSuggestions(d.internal_links_suggested);
        }

        if (d.image_error) {
            aiSetStatus(`✓ Artículo generado. Imagen falló: ${d.image_error}`, true);
            document.getElementById('ai-spinner').style.display = 'none';
        } else {
            aiSetStatus('✓ Artículo generado. Revisa y guarda.', true);
            document.getElementById('ai-spinner').style.display = 'none';
        }
    } catch (e) {
        aiSetError('Error de conexión: ' + e.message);
    } finally {
        document.getElementById('ai-generate-btn').disabled = false;
    }
});

// Solo imagen (artículo existente)
document.getElementById('ai-image-btn')?.addEventListener('click', async () => {
    if (!AI_IMAGE_URL) return;
    aiSetStatus('Generando imagen... (puede tardar hasta 2 minutos)');
    const prompt = document.querySelector('[name="titulo"]')?.value || '';
    try {
        const res = await aiPost(AI_IMAGE_URL, { prompt });
        if (res.ok) {
            aiSetField('imagen_principal', res.url);
            aiSetStatus('✓ Imagen generada.');
            document.getElementById('ai-spinner').style.display = 'none';
        } else {
            aiSetError(res.message);
        }
    } catch (e) {
        aiSetError(e.message);
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
        contenido:     document.querySelector('[name="contenido"]')?.value,
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
