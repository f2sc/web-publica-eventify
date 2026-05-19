@extends('layouts.admin')

@section('title', 'Configuración IA')

@section('content')
<div class="admin-header">
    <h1>✦ Configuración de IA</h1>
    <p style="color:#6b7280;margin-top:.25rem">Proveedores, modelos, API keys y prompts del asistente editorial.</p>
</div>

@if(session('success'))
<div class="alert alert-success" style="margin-bottom:1.5rem">{{ session('success') }}</div>
@endif

<form method="POST" action="{{ route('admin.ia.config.update') }}">
@method('PUT')
@csrf

{{-- Sección: Proveedor de Texto --}}
<div class="admin-card" style="margin-bottom:1.5rem">
    <h2 style="font-size:1rem;font-weight:700;margin-bottom:1.25rem;color:#374151;border-bottom:1px solid #f3f4f6;padding-bottom:.75rem">
        📝 Proveedor de Texto
    </h2>
    <div class="form-row">
        <div class="form-group">
            <label class="form-label">Proveedor</label>
            <select name="text_provider" class="form-control" id="text_provider" onchange="updateTextModels()">
                <option value="claude" {{ $config->text_provider === 'claude' ? 'selected' : '' }}>Claude (Anthropic)</option>
                <option value="openai" {{ $config->text_provider === 'openai' ? 'selected' : '' }}>OpenAI (GPT)</option>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Modelo</label>
            <div style="display:flex;gap:.5rem;align-items:flex-start">
                <select name="text_model" id="text_model" class="form-control" style="flex:1">
                    <option value="{{ old('text_model', $config->text_model) }}" selected>
                        {{ old('text_model', $config->text_model) ?: '— sin modelo guardado —' }}
                    </option>
                </select>
                <button type="button" onclick="loadModels('text')" id="load-text-btn"
                    class="btn btn-secondary" style="white-space:nowrap;flex-shrink:0" title="Conectar con la API y descargar modelos disponibles">
                    ↓ Cargar
                </button>
            </div>
            <small id="text-model-status" style="color:#9ca3af;font-size:.75rem"></small>
        </div>
    </div>
    <div class="form-group">
        <label class="form-label">API Key</label>
        <div style="display:flex;gap:.5rem;align-items:flex-start">
            <input type="password" name="text_api_key" id="text_api_key" class="form-control" placeholder="Deja vacío para no cambiarla" autocomplete="new-password" style="flex:1">
            <button type="button" onclick="testConn('text')" class="btn btn-secondary" style="white-space:nowrap;flex-shrink:0">Probar</button>
        </div>
        @if($config->text_api_key)
        <small style="color:#10b981;font-size:.75rem">✓ API key guardada (cifrada)</small>
        @else
        <small style="color:#ef4444;font-size:.75rem">⚠ Sin API key configurada</small>
        @endif
        <div id="test-text-result" style="margin-top:.5rem;font-size:.8rem;display:none"></div>
    </div>
</div>

{{-- Sección: Proveedor de Imagen --}}
<div class="admin-card" style="margin-bottom:1.5rem">
    <h2 style="font-size:1rem;font-weight:700;margin-bottom:1.25rem;color:#374151;border-bottom:1px solid #f3f4f6;padding-bottom:.75rem">
        🖼 Proveedor de Imagen
    </h2>
    <div class="form-row">
        <div class="form-group">
            <label class="form-label">Proveedor</label>
            <select name="image_provider" id="image_provider" class="form-control" onchange="loadModels('image')">
                <option value="google" {{ $config->image_provider === 'google' ? 'selected' : '' }}>Google Imagen</option>
                <option value="openai" {{ $config->image_provider === 'openai' ? 'selected' : '' }}>OpenAI DALL-E</option>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Modelo</label>
            <div style="display:flex;gap:.5rem;align-items:flex-start">
                <select name="image_model" id="image_model" class="form-control" style="flex:1">
                    <option value="{{ old('image_model', $config->image_model) }}" selected>
                        {{ old('image_model', $config->image_model) ?: '— sin modelo guardado —' }}
                    </option>
                </select>
                <button type="button" onclick="loadModels('image')" id="load-image-btn"
                    class="btn btn-secondary" style="white-space:nowrap;flex-shrink:0" title="Conectar con la API y descargar modelos disponibles">
                    ↓ Cargar
                </button>
            </div>
            <small id="image-model-status" style="color:#9ca3af;font-size:.75rem"></small>
        </div>
    </div>
    <div class="form-row">
        <div class="form-group">
            <label class="form-label">API Key</label>
            <div style="display:flex;gap:.5rem;align-items:flex-start">
                <input type="password" name="image_api_key" id="image_api_key" class="form-control" placeholder="Deja vacío para no cambiarla" autocomplete="new-password" style="flex:1">
                <button type="button" onclick="testConn('image')" class="btn btn-secondary" style="white-space:nowrap;flex-shrink:0">Probar</button>
            </div>
            @if($config->image_api_key)
            <small style="color:#10b981;font-size:.75rem">✓ API key guardada (cifrada)</small>
            @else
            <small style="color:#ef4444;font-size:.75rem">⚠ Sin API key configurada</small>
            @endif
            <div id="test-image-result" style="margin-top:.5rem;font-size:.8rem;display:none"></div>
        </div>
        <div class="form-group">
            <label class="form-label">Tamaño por defecto</label>
            <select name="image_size" class="form-control">
                @foreach(['1024x1024', '1024x1792', '1792x1024'] as $size)
                <option value="{{ $size }}" {{ $config->image_size === $size ? 'selected' : '' }}>{{ $size }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>

{{-- Sección: Parámetros globales --}}
<div class="admin-card" style="margin-bottom:1.5rem">
    <h2 style="font-size:1rem;font-weight:700;margin-bottom:1.25rem;color:#374151;border-bottom:1px solid #f3f4f6;padding-bottom:.75rem">
        ⚙ Parámetros Globales
    </h2>
    <div class="form-row">
        <div class="form-group">
            <label class="form-label">Tono por defecto</label>
            <input type="text" name="default_tone" class="form-control" value="{{ old('default_tone', $config->default_tone) }}" placeholder="profesional y cercano">
        </div>
        <div class="form-group">
            <label class="form-label">Longitud por defecto</label>
            <input type="text" name="default_length" class="form-control" value="{{ old('default_length', $config->default_length) }}" placeholder="1000-1500 palabras">
        </div>
    </div>
    <div class="form-row">
        <div class="form-group">
            <label class="form-label">Máx. artículos contexto para interlinking</label>
            <input type="number" name="max_articles_context" class="form-control" value="{{ old('max_articles_context', $config->max_articles_context) }}" min="1" max="20">
        </div>
        <div class="form-group">
            <label class="form-label">Máx. enlaces internos sugeridos</label>
            <input type="number" name="max_internal_links" class="form-control" value="{{ old('max_internal_links', $config->max_internal_links) }}" min="0" max="10">
        </div>
    </div>
    <div class="form-row" style="gap:2rem;align-items:center">
        <label style="display:flex;align-items:center;gap:.5rem;font-size:.875rem;cursor:pointer">
            <input type="hidden" name="always_draft" value="0">
            <input type="checkbox" name="always_draft" value="1" {{ $config->always_draft ? 'checked' : '' }}>
            Guardar siempre como borrador
        </label>
        <label style="display:flex;align-items:center;gap:.5rem;font-size:.875rem;cursor:pointer">
            <input type="hidden" name="auto_generate_faq" value="0">
            <input type="checkbox" name="auto_generate_faq" value="1" {{ $config->auto_generate_faq ? 'checked' : '' }}>
            Generar FAQ automáticamente
        </label>
        <label style="display:flex;align-items:center;gap:.5rem;font-size:.875rem;cursor:pointer">
            <input type="hidden" name="auto_generate_image" value="0">
            <input type="checkbox" name="auto_generate_image" value="1" {{ $config->auto_generate_image ? 'checked' : '' }}>
            Generar imagen automáticamente
        </label>
    </div>
    <input type="hidden" name="default_language" value="es">
</div>

{{-- Sección: Prompts editables --}}
<div class="admin-card" style="margin-bottom:1.5rem">
    <h2 style="font-size:1rem;font-weight:700;margin-bottom:1.25rem;color:#374151;border-bottom:1px solid #f3f4f6;padding-bottom:.75rem">
        🧠 Prompts Internos
        <small style="font-weight:400;color:#9ca3af;font-size:.8rem"> — deja vacío para usar los prompts por defecto</small>
    </h2>
    <div class="form-group">
        <label class="form-label">System Prompt (instrucciones base para la IA de texto)</label>
        <textarea name="prompt_system" class="form-control" rows="6" placeholder="Deja vacío para usar el prompt por defecto...">{{ old('prompt_system', $config->prompt_system) }}</textarea>
    </div>
    <div class="form-group">
        <label class="form-label">Prompt de Imagen (estilo y contexto para la generación de imágenes)</label>
        <textarea name="prompt_image" class="form-control" rows="3" placeholder="Ej: Fotografía profesional, realista, sin texto...">{{ old('prompt_image', $config->prompt_image) }}</textarea>
    </div>
    <div class="form-group">
        <label class="form-label">Prompt de Interlinking (instrucciones para enlazado interno)</label>
        <textarea name="prompt_interlinking" class="form-control" rows="3" placeholder="Instrucciones para que la IA sugiera enlaces internos...">{{ old('prompt_interlinking', $config->prompt_interlinking) }}</textarea>
    </div>
</div>

<div style="display:flex;gap:1rem;align-items:center">
    <button type="submit" class="btn btn-primary">Guardar configuración</button>
    <a href="{{ route('admin.ia.logs') }}" style="font-size:.875rem;color:#6b7280">Ver logs de generación →</a>
</div>

</form>

@push('scripts')
<script>
const TEST_URL        = '{{ route('admin.ia.test') }}';
const FETCH_MODELS_URL = '{{ route('admin.ia.fetch-models') }}';
const CSRF_TOKEN      = document.querySelector('meta[name="csrf-token"]').content;

async function apiPost(url, body) {
    const r = await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
        body: JSON.stringify(body),
    });
    return r.json();
}

// ─── Cargar modelos desde la API del proveedor ───────────────────────────────
async function loadModels(type) {
    const provider   = document.getElementById(type + '_provider').value;
    const apiKey     = document.getElementById(type + '_api_key').value;
    const select     = document.getElementById(type + '_model');
    const statusEl   = document.getElementById(type + '-model-status');
    const btn        = document.getElementById('load-' + type + '-btn');
    const currentVal = select.value;

    btn.disabled     = true;
    btn.textContent  = '⟳ Cargando…';
    statusEl.style.color = '#6b7280';
    statusEl.textContent = 'Conectando con la API…';

    try {
        const json = await apiPost(FETCH_MODELS_URL, { type, provider, api_key: apiKey });

        if (!json.ok || !json.models.length) {
            statusEl.style.color = '#ef4444';
            statusEl.textContent = json.message || 'Sin modelos devueltos.';
            return;
        }

        // Reconstruir opciones manteniendo la selección actual
        select.innerHTML = '';
        json.models.forEach(m => {
            const opt = document.createElement('option');
            opt.value = m;
            opt.textContent = m;
            if (m === currentVal) opt.selected = true;
            select.appendChild(opt);
        });

        // Si el modelo actual no está en la lista, añadirlo al principio
        if (currentVal && !json.models.includes(currentVal)) {
            const opt = document.createElement('option');
            opt.value = currentVal;
            opt.textContent = currentVal + ' (guardado)';
            opt.selected = true;
            select.prepend(opt);
        }

        statusEl.style.color = '#10b981';
        statusEl.textContent = `✓ ${json.models.length} modelos cargados.`;

    } catch (e) {
        statusEl.style.color = '#ef4444';
        statusEl.textContent = 'Error: ' + e.message;
    } finally {
        btn.disabled    = false;
        btn.textContent = '↓ Cargar';
    }
}

// ─── Probar conexión con la API ──────────────────────────────────────────────
async function testConn(type) {
    const provider  = document.getElementById(type + '_provider').value;
    const apiKey    = document.getElementById(type + '_api_key').value;
    const model     = document.getElementById(type + '_model').value;
    const resultDiv = document.getElementById('test-' + type + '-result');

    resultDiv.style.display = 'block';
    resultDiv.style.color   = '#6b7280';
    resultDiv.textContent   = '⏳ Probando conexión…';

    try {
        const json = await apiPost(TEST_URL, { type, provider, api_key: apiKey, model });
        resultDiv.style.color = json.ok ? '#10b981' : '#ef4444';
        resultDiv.textContent = json.message;
    } catch (e) {
        resultDiv.style.color = '#ef4444';
        resultDiv.textContent = 'Error de red: ' + e.message;
    }
}
</script>
@endpush
@endsection
