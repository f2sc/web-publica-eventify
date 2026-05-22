<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Articulo;
use App\Models\CategoriaBlog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ArticuloController extends Controller
{
    public function index()
    {
        $articulos = Articulo::with('categoriaBlog')->orderByDesc('created_at')->paginate(20);
        return view('admin.articulos.index', compact('articulos'));
    }

    public function create()
    {
        $categorias = CategoriaBlog::orderBy('nombre')->get();
        $series     = \App\Models\Serie::orderBy('nombre')->get();
        return view('admin.articulos.create', compact('categorias', 'series'));
    }

    public function store(Request $request)
    {
        $data = $this->validar($request);
        $data['slug'] = $data['slug'] ?: Str::slug($data['titulo']);

        $data = $this->resolverCategoria($request, $data);

        Articulo::create($data);

        return redirect()->route('admin.articulos.index')
            ->with('success', 'Artículo creado correctamente.');
    }

    public function show(Articulo $articulo)
    {
        return redirect()->route('admin.articulos.edit', $articulo);
    }

    public function edit(Articulo $articulo)
    {
        $categorias = CategoriaBlog::orderBy('nombre')->get();
        $series     = \App\Models\Serie::orderBy('nombre')->get();
        return view('admin.articulos.edit', compact('articulo', 'categorias', 'series'));
    }

    public function update(Request $request, Articulo $articulo)
    {
        $data = $this->validar($request);
        $data['slug'] = $data['slug'] ?: Str::slug($data['titulo']);

        $data = $this->resolverCategoria($request, $data);

        $articulo->update($data);

        return redirect()->route('admin.articulos.index')
            ->with('success', 'Artículo actualizado correctamente.');
    }

    public function destroy(Articulo $articulo)
    {
        $articulo->delete();

        return redirect()->route('admin.articulos.index')
            ->with('success', 'Artículo eliminado.');
    }

    public function preview(Articulo $articulo)
    {
        return app(\App\Http\Controllers\BlogController::class)->showPreview($articulo);
    }

    public function updateEstado(Articulo $articulo, Request $request): JsonResponse
    {
        $request->validate(['estado' => ['required', 'in:borrador,programado,publicado,archivado']]);
        $articulo->update(['estado' => $request->input('estado')]);
        return response()->json(['ok' => true]);
    }

    public function uploadImagen(Request $request): JsonResponse
    {
        $request->validate(['image' => ['required', 'image', 'max:5120']]);

        $file     = $request->file('image');
        $ext      = strtolower($file->getClientOriginalExtension()) ?: 'jpg';
        $baseName = $this->buildUploadBaseName($request);
        $relPath  = 'articulos/' . $baseName . '.' . $ext;

        // Evitar sobreescribir si ya existe otro fichero con ese nombre
        $i = 1;
        while (\Illuminate\Support\Facades\Storage::disk('public')->exists($relPath)) {
            $relPath = 'articulos/' . $baseName . '-' . $i . '.' . $ext;
            $i++;
        }

        \Illuminate\Support\Facades\Storage::disk('public')->putFileAs(
            'articulos',
            $file,
            basename($relPath)
        );

        return response()->json([
            'ok'  => true,
            'url' => \Illuminate\Support\Facades\Storage::disk('public')->url($relPath),
        ]);
    }

    private function buildUploadBaseName(Request $request): string
    {
        // Intentar con article_id → slug del artículo guardado
        if ($request->filled('article_id')) {
            $articulo = Articulo::find((int) $request->input('article_id'));
            if ($articulo) {
                $base = $articulo->focus_keyword ?: $articulo->slug ?: $articulo->titulo ?: '';
                $slug = \Illuminate\Support\Str::slug($base);
                $slug = substr($slug, 0, 70);
                if ($slug) {
                    return $slug . '-imagen-principal';
                }
            }
        }

        // Fallback: slug enviado desde el formulario (artículo nuevo, aún no guardado)
        if ($request->filled('article_slug')) {
            $slug = \Illuminate\Support\Str::slug($request->input('article_slug'));
            $slug = substr($slug, 0, 70);
            if ($slug) {
                return $slug . '-imagen-principal';
            }
        }

        // Último recurso: nombre original del fichero sanitizado
        $original = pathinfo($request->file('image')->getClientOriginalName(), PATHINFO_FILENAME);
        $slug     = \Illuminate\Support\Str::slug($original);
        return $slug ?: ('imagen-blog-' . now()->format('YmdHis'));
    }

    public function updateFecha(Articulo $articulo, Request $request): JsonResponse
    {
        $request->validate(['fecha' => ['nullable', 'date']]);
        $articulo->update(['fecha_publicacion' => $request->input('fecha')]);
        return response()->json(['ok' => true]);
    }

    // Si el usuario seleccionó "nueva", crea la categoría y devuelve su ID + nombre
    private function resolverCategoria(Request $request, array $data): array
    {
        $idRaw = $request->input('categoria_blog_id');

        if ($idRaw === 'nueva') {
            $nombre = trim($request->input('categoria_nueva_nombre', ''));
            if ($nombre !== '') {
                $cat = CategoriaBlog::firstOrCreate(
                    ['slug' => Str::slug($nombre)],
                    [
                        'nombre'           => $nombre,
                        'descripcion'      => trim($request->input('categoria_nueva_descripcion', '')),
                        'meta_description' => trim($request->input('categoria_nueva_meta_description', '')),
                    ]
                );
                $data['categoria_blog_id'] = $cat->id;
                $data['categoria_blog']    = $cat->nombre;
            } else {
                $data['categoria_blog_id'] = null;
                $data['categoria_blog']    = null;
            }
        } elseif ($idRaw && is_numeric($idRaw)) {
            $cat = CategoriaBlog::find((int) $idRaw);
            $data['categoria_blog_id'] = $cat?->id;
            $data['categoria_blog']    = $cat?->nombre;
        } else {
            $data['categoria_blog_id'] = null;
            $data['categoria_blog']    = null;
        }

        return $data;
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'titulo'              => ['required', 'string', 'max:255'],
            'slug'                => ['nullable', 'string', 'max:255'],
            'extracto'            => ['nullable', 'string'],
            'contenido'           => ['nullable', 'string'],
            'imagen_principal'    => ['nullable', 'string', 'max:500'],
            'image_alt'           => ['nullable', 'string', 'max:255'],
            'focus_keyword'       => ['nullable', 'string', 'max:150'],
            'etiquetas'           => ['nullable', 'string', 'max:255'],
            'meta_title'          => ['nullable', 'string', 'max:255'],
            'meta_description'    => ['nullable', 'string', 'max:320'],
            'canonical'           => ['nullable', 'url', 'max:255'],
            'indexable'           => ['boolean'],
            'og_image'            => ['nullable', 'string', 'max:500'],
            'schema_type'         => ['required', 'string', 'in:BlogPosting,Article,HowTo'],
            'faq_json'            => ['nullable', 'string'],
            'autor'               => ['nullable', 'string', 'max:100'],
            'estado'              => ['required', 'in:borrador,programado,publicado,archivado'],
            'fecha_publicacion'   => ['nullable', 'date_format:Y-m-d\TH:i'],
            'ai_context_summary'  => ['nullable', 'string'],
            'summary_short'       => ['nullable', 'string', 'max:255'],
            'serie_id'            => ['nullable', 'integer', 'exists:series,id'],
            'orden_en_serie'      => ['nullable', 'integer', 'min:1'],
            'enviar_newsletter'   => ['boolean'],
        ]);
    }
}
