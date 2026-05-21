# Implementación de IA en Mini-CRM — Conversación con ChatGPT

> Conversación original exportada de ChatGPT. Fecha estimada: mayo 2026.

---

## Pregunta inicial del usuario

Tengo hecho el mini CRM de la página pública, este tiene un CRUD de artículos y de categorías, lo mantengo simple. Pero está altamente preparado para SEO, tiene muchos campos para crear el SEO. El problema es que se me hace costoso rellenar todos esos campos a mano por cada artículo.

**Mi idea:** meterle una IA interna en la web que le pueda pasar un texto con la idea que quiero desarrollar y algunos detalles y me cree el artículo completo con todo lo de SEO e incluso la imagen.

¿Cómo hacer esto y de qué forma lo más económica posible?

---

## Respuesta ChatGPT — Arquitectura y enfoque

Sí, tiene mucho sentido meterlo en tu mini CRM. Para tu caso yo no montaría "un chat con IA" genérico, sino un **asistente editorial interno** dentro del CRUD de artículos.

**La idea sería:**
1. Tú escribes una idea corta.
2. La IA genera el artículo completo.
3. Rellena automáticamente todos los campos SEO.
4. Genera slug, metatítulo, metadescripción, extracto, etiquetas, schema, FAQ, imagen destacada y prompt de imagen.
5. Tú revisas.
6. Guardas como borrador o publicas.

### Opción más económica y práctica

Para empezar usaría **OpenAI GPT-5 mini** para texto. Para imagen, **Gemini Imagen 4 Fast** o **Gemini 2.5 Flash Image**.

- GPT-5 nano: 0,05 $/1M tokens entrada, 0,40 $/1M salida
- GPT-5 mini: 0,25 $/1M entrada, 2,00 $/1M salida
- Imagen 4 Fast: ~0,02 $/imagen
- Gemini 2.5 Flash Image: ~0,039 $/imagen hasta 1024x1024

### Flujo propuesto en el CRM

Botón **"Crear artículo con IA"** con formulario:

| Campo | Descripción |
|-------|-------------|
| Idea principal | Texto libre |
| Tipo de artículo | SEO / HowTo / Noticia |
| Público objetivo | Bares, comercios, asociaciones... |
| Localidad / zona | Madrid, Coslada... |
| Servicio o producto relacionado | |
| Tono | Cercano, profesional, comercial |
| Palabras clave principales | |
| Palabras clave secundarias | |
| Longitud | |
| ¿Crear imagen destacada? | Sí / No |
| ¿Publicar como borrador? | Sí / No |

### Salida ideal como JSON

```json
{
  "title": "Cómo fidelizar clientes en un bar usando códigos QR",
  "slug": "fidelizar-clientes-bar-codigo-qr",
  "excerpt": "Descubre cómo un bar puede usar códigos QR...",
  "content": "...artículo completo en HTML o Markdown...",
  "seo_title": "Cómo fidelizar clientes en un bar con códigos QR",
  "seo_description": "Guía práctica para bares...",
  "focus_keyword": "fidelizar clientes bar",
  "secondary_keywords": ["código QR para bares", "campañas para restaurantes"],
  "tags": ["hostelería", "marketing local", "fidelización"],
  "category_suggestion": "Marketing para comercios",
  "faq": [{"question": "¿Sirve un código QR?", "answer": "Sí, porque..."}],
  "image_prompt": "Imagen realista de un pequeño bar español..."
}
```

### Campos SEO que genera la IA

| Campo | Lo genera la IA |
|-------|----------------|
| Título del artículo | Sí |
| Slug | Sí |
| Extracto | Sí |
| Contenido completo | Sí |
| Meta title | Sí |
| Meta description | Sí |
| Keyword principal | Sí |
| Keywords secundarias | Sí |
| Categoría sugerida | Sí |
| Etiquetas | Sí |
| Texto ALT de imagen | Sí |
| Prompt para imagen | Sí |
| FAQ SEO | Sí |
| Schema JSON-LD | Sí |
| Open Graph title | Sí |
| Open Graph description | Sí |
| Twitter title/description | Sí |

**Importante:** no dejaría que publique directamente. Siempre como borrador.

### Flujo recomendado en 3 fases

**Fase 1 — Generador de artículo SEO** (solo texto)
- Título, slug, artículo completo, extracto, SEO title, SEO description, keywords, tags, FAQ, ALT de imagen
- Esto ya ahorra el 80% del trabajo.

**Fase 2 — Generador de imagen**
- IA de texto genera el prompt de imagen.
- Sistema llama API de imagen.
- Guarda imagen en servidor.
- Asocia al artículo como imagen destacada.
- Guarda ALT, título y prompt usado.

**Fase 3 — Reoptimización SEO**
- Botones: Mejorar SEO, crear 5 títulos alternativos, crear metadescripción alternativa, crear FAQ, crear versión para LinkedIn, crear email relacionado, crear imagen nueva.

### División de modelos por tarea (coste)

| Tarea | Modelo recomendado |
|-------|-------------------|
| Crear artículo completo | GPT-5 mini |
| Crear títulos/metas/tags | GPT-5 nano |
| Crear FAQ/schema | GPT-5 nano o mini |
| Revisar calidad final | GPT-5 mini |
| Imagen destacada | Imagen 4 Fast / Gemini 2.5 Flash Image |
| Artículo muy importante | GPT-5 o GPT-5.4 mini |

### Coste aproximado por artículo

- Texto con GPT-5 mini: céntimos bajos
- Imagen: 0,02–0,04 $ si usas Imagen 4 Fast/Standard

### Medidas anti-gasto desde el principio

Tabla de logs de generación:
```
ai_generations
- id
- user_id
- article_id
- provider
- model
- type: article / seo / image / faq
- input_tokens
- output_tokens
- estimated_cost
- status
- created_at
```

Separar acciones: generar artículo completo / regenerar solo título / regenerar solo meta description / regenerar solo imagen.

### Estructura técnica

```
/articles
/articles/{id}/ai-generate
/articles/{id}/ai-regenerate-seo
/articles/{id}/ai-generate-image
```

```
AiContentService
AiImageService
SeoArticleBuilder
AiCostLogger
```

Capa de abstracción de proveedores:
```
ArticleController
  → AiArticleService
      → OpenAiProvider
      → GeminiImageProvider
      → ArticleSeoMapper
```

---

## Comparativa Claude vs GPT

**¿Claude hace imágenes?** No. Claude puede generar diagramas, SVG, HTML visual, analizar imágenes, pero no genera fotos ni ilustraciones realistas.

| Uso | Mejor opción |
|-----|-------------|
| Artículo largo bien redactado | Claude Sonnet / GPT-5 mini |
| Campos SEO estructurados en JSON | GPT suele ser muy cómodo |
| Redacción más natural | Claude suele ir muy bien |
| Generar schema, FAQ, JSON-LD | GPT suele ser más fiable estructurando |
| Coste bajo masivo | GPT-5 nano / GPT-5 mini |
| Imagen destacada | GPT/OpenAI o Google Imagen, no Claude |

**Costes API:**
- OpenAI GPT-5 mini: 0,25 $/1M entrada, 2,00 $/1M salida
- OpenAI GPT-5 nano: 0,05 $/1M entrada, 0,40 $/1M salida
- Claude Haiku 4.5: 1 $/1M entrada, 5 $/1M salida
- Claude Sonnet: más caro que Haiku

---

## Decisión del usuario: Opción C (híbrida)

> "Entonces será la opción C. Habrá que tener una página de configuración de modelos donde seleccione el que quiero para generación de imágenes y para generación de textos. Quiero tener un campo donde exponga la idea y de ahí me cree todo el texto y la imagen, llamando a las IAs correspondientes. Eso me lo generará directamente en la página de nuevo artículo. Un botón de regenerar con IA (las estrellitas ✨) en cada campo. Al enviar la idea principal también debe tener en cuenta mínimamente artículos anteriores por si les puede hacer mención en algún enlace de forma sutil. Para esto cada artículo debería tener un resumen total de qué trata para que lo pueda consultar los nuevos artículos."

---

## Diseño completo — Opción C Híbrida

### 1. Página de configuración de modelos (`Configuración > IA`)

**A. Proveedor de texto principal**
- Proveedor: Claude / OpenAI
- Modelo: Claude Sonnet, GPT-5 mini, GPT-5 nano...
- API Key
- Activar / desactivar

**B. Proveedor de estructuración SEO**
- Proveedor: OpenAI / Claude
- Modelo: GPT-5 mini, GPT-5 nano...
- API Key

**C. Proveedor de imagen**
- Proveedor: OpenAI Images, Gemini/Imagen, otro
- Modelo de imagen
- API Key
- Tamaño por defecto
- Calidad por defecto
- Estilo por defecto

**D. Parámetros globales**
- Idioma por defecto
- Tono por defecto
- Longitud por defecto
- Nº máximo de artículos anteriores a consultar
- Nº máximo de enlaces internos a sugerir
- Crear imagen automáticamente: sí/no
- Crear FAQ automáticamente: sí/no
- Guardar como borrador siempre: sí/no

**E. Prompts internos editables**
- Prompt de redacción
- Prompt de estructuración SEO
- Prompt de imagen
- Prompt de enlazado interno

---

### 2. Página "Nuevo artículo" con generación desde la idea

**Bloque superior: Asistente IA**
- Idea principal (textarea grande)
- Categoría
- Keyword principal
- Tono
- Localidad
- Detalles adicionales
- ☐ Generar imagen
- ☐ Generar FAQ
- ☐ Sugerir enlaces internos
- ☐ Guardar como borrador

Botón principal: **Generar con IA**

Botones alternativos:
- Generar solo texto
- Generar solo SEO
- Generar solo imagen

**Bloque de edición del artículo**
- título ✨, slug ✨, extracto ✨, contenido ✨
- categoría, etiquetas ✨, imagen destacada ✨

**Bloque SEO**
- SEO title ✨, SEO description ✨
- keyword principal ✨, keywords secundarias ✨
- OG title ✨, OG description ✨
- FAQ ✨, schema ✨

**Bloque IA / contexto interno** *(solo admin)*
- resumen corto
- resumen IA interno
- artículos relacionados detectados
- enlaces internos sugeridos
- logs de generación
- proveedor/modelos usados

---

### 3. Flujo interno al pulsar "Generar con IA"

**Fase 1 — Recoger contexto**
- idea principal, detalles, categoría, keyword, tono, localidad, configuración global
- Busca artículos anteriores relevantes

**Fase 2 — Consultar artículos anteriores**
- Buscar relacionados por categoría, keywords, coincidencia semántica
- Seleccionar los 3–8 más relevantes
- Enviar a la IA solo: título, slug, resumen, keyword principal

**Fase 3 — Redacción con Claude**
- Redacta artículo completo
- Mantiene tono natural
- Sugiere puntos de enlazado interno
- Devuelve también resumen del artículo creado
- Devuelve: título sugerido, resumen, contenido, ideas de FAQ, keywords sugeridas, sugerencias de enlaces

**Fase 4 — Estructuración SEO con GPT**
- Toma el contenido de Claude + idea inicial
- Devuelve JSON limpio:
  - título definitivo, slug, extracto, contenido
  - meta title, meta description
  - keyword principal, keywords secundarias
  - tags, h1, h2 sugeridos
  - FAQ, schema FAQ/Article
  - open graph, twitter metadata
  - alt de imagen, prompt de imagen
  - enlaces internos ordenados

**Fase 5 — Generación de imagen**
- Claude/GPT genera prompt final de imagen
- Llama al proveedor de imagen configurado
- Guarda imagen en servidor
- Asocia al artículo con: alt, title, leyenda, prompt usado, proveedor usado

**Fase 6 — Volcado en formulario**
- Muestra todo relleno
- Usuario revisa y edita
- Guarda como borrador o publica manualmente

---

### 4. Botón "regenerar con IA" ✨ en cada campo

**Campos con regeneración individual:**
- título, slug, extracto, contenido
- meta title, meta description
- keyword principal, keywords secundarias
- tags, FAQ, schema
- prompt de imagen, alt de imagen

**Lógica de regeneración:**

*Regenerar título:* usa idea principal + contenido actual + keyword → devuelve solo nuevo título.

*Regenerar meta description:* usa título + contenido + keyword → devuelve solo meta description.

*Regenerar contenido:* usa idea + categoría + tono + enlaces sugeridos → nueva versión del artículo.

*Regenerar imagen:* usa título + extracto + keyword + prompt → nueva imagen.

---

### 5. Nuevo campo `ai_context_summary` por artículo

Resumen pensado para la IA (no para el usuario):

> "Artículo que explica cómo los comercios locales pueden captar clientes mediante códigos QR en tienda. Se centra en captación inicial, registro sin app y uso posterior para campañas. Relacionado con marketing local, fidelización y promociones para pequeños negocios."

**Nuevos campos recomendados en la tabla de artículos:**

*Editoriales:*
- `summary_short`
- `summary_long`
- `focus_keyword`
- `secondary_keywords`
- `topic_cluster`
- `target_audience`

*Técnicos / IA:*
- `ai_context_summary`
- `ai_generated` (boolean)
- `ai_last_provider_text`
- `ai_last_provider_seo`
- `ai_last_provider_image`
- `ai_last_prompt_text`
- `ai_last_prompt_seo`
- `ai_last_prompt_image`

*Enlazado interno:*
- `internal_links_suggested` (JSON)
- `internal_links_used` (JSON)

---

### 6. Lógica de enlazado interno sutil

**Regla:** máximo 1–3 enlaces internos por artículo (salvo artículos muy largos).

**Proceso:**
1. Sistema busca artículos relacionados → pasa a la IA: título, slug, resumen, keyword
2. IA decide si enlazar, con qué anchor text y en qué parte del contenido

**Ejemplo de salida:**
```json
{
  "anchor": "campañas para comercios locales",
  "url": "/blog/campanas-comercios-locales",
  "motivo": "complementa la idea del artículo actual"
}
```

---

### 7. Optimización de coste

- No mandar todo el histórico de artículos, solo los relacionados (3–8 máximo)
- No regenerar el artículo completo para cambios pequeños
- Generación por pasos: Claude redacta → GPT estructura → imagen solo si hace falta
- Guardar logs de coste desde el primer día

---

### 8. MVP recomendado

**Fase 1 (MVP mínimo):**
- Página de configuración de IA
- Selección de modelo texto, SEO e imagen
- Campo "idea principal" en nuevo artículo
- Botón "generar con IA"
- Relleno automático: título, slug, extracto, contenido, SEO title, SEO description, keyword, tags, resumen IA
- Guardado como borrador

**Fase 2:**
- Generación de imagen
- Alt automático
- Prompt de imagen editable
- Botón regenerar imagen

**Fase 3:**
- Botones ✨ en cada campo
- Enlaces internos sugeridos
- Consulta de artículos anteriores por resumen
- Tabla de logs y costes

---

### 9. Arquitectura técnica conceptual

```
Entrada
└── Idea principal + detalles

Paso 1: Claude redacta el artículo base

Paso 2: GPT transforma y rellena todos los campos SEO

Paso 3: Sistema consulta resúmenes de artículos anteriores
        → sugiere enlaces internos

Paso 4: API de imagen genera imagen destacada

Paso 5: Todo se vuelca en el formulario de nuevo artículo

Paso 6: Usuario revisa, ajusta, guarda como borrador o publica
```

**Decisión final de modelos:**
- Texto principal: Claude Sonnet (calidad editorial superior)
- Estructuración SEO: GPT-5 mini/nano (fiable estructurando JSON)
- Imagen: Google Imagen 4 Fast o Gemini 2.5 Flash Image
- Publicación: siempre borrador
- Costes: guardar logs desde el primer día
