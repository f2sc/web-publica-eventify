<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Articulo;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ArticuloController extends Controller
{
    public function index()
    {
        $articulos = Articulo::orderByDesc('created_at')->paginate(20);
        return view('admin.articulos.index', compact('articulos'));
    }

    public function create()
    {
        return view('admin.articulos.create');
    }

    public function store(Request $request)
    {
        $data = $this->validar($request);
        $data['slug'] = $data['slug'] ?: Str::slug($data['titulo']);

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
        return view('admin.articulos.edit', compact('articulo'));
    }

    public function update(Request $request, Articulo $articulo)
    {
        $data = $this->validar($request);
        $data['slug'] = $data['slug'] ?: Str::slug($data['titulo']);

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

    private function validar(Request $request): array
    {
        return $request->validate([
            'titulo'            => ['required', 'string', 'max:255'],
            'slug'              => ['nullable', 'string', 'max:255'],
            'extracto'          => ['nullable', 'string'],
            'contenido'         => ['nullable', 'string'],
            'imagen_principal'  => ['nullable', 'string', 'max:255'],
            'categoria_blog'    => ['nullable', 'string', 'max:100'],
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
