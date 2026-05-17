<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Articulo;
use App\Models\CategoriaBlog;
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
        return view('admin.articulos.create', compact('categorias'));
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
        return view('admin.articulos.edit', compact('articulo', 'categorias'));
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
            'titulo'            => ['required', 'string', 'max:255'],
            'slug'              => ['nullable', 'string', 'max:255'],
            'extracto'          => ['nullable', 'string'],
            'contenido'         => ['nullable', 'string'],
            'imagen_principal'  => ['nullable', 'string', 'max:255'],
            'etiquetas'         => ['nullable', 'string', 'max:255'],
            'meta_title'        => ['nullable', 'string', 'max:255'],
            'meta_description'  => ['nullable', 'string', 'max:320'],
            'canonical'         => ['nullable', 'url', 'max:255'],
            'indexable'         => ['boolean'],
            'og_image'          => ['nullable', 'string', 'max:255'],
            'schema_type'       => ['required', 'string', 'in:BlogPosting,Article,HowTo'],
            'autor'             => ['nullable', 'string', 'max:100'],
            'estado'            => ['required', 'in:borrador,publicado,archivado'],
            'fecha_publicacion' => ['nullable', 'date'],
        ]);
    }
}
