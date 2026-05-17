<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CategoriaBlog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoriaBlogController extends Controller
{
    public function index()
    {
        $categorias = CategoriaBlog::withCount('articulos')->orderBy('nombre')->get();
        return view('admin.categorias.index', compact('categorias'));
    }

    public function create()
    {
        return view('admin.categorias.create');
    }

    public function store(Request $request)
    {
        $data = $this->validar($request);
        $data['slug'] = $data['slug'] ?: Str::slug($data['nombre']);

        CategoriaBlog::create($data);

        return redirect()->route('admin.categorias.index')
            ->with('success', 'Categoría creada correctamente.');
    }

    public function edit(CategoriaBlog $categoria)
    {
        return view('admin.categorias.edit', compact('categoria'));
    }

    public function update(Request $request, CategoriaBlog $categoria)
    {
        $data = $this->validar($request, $categoria->id);
        $data['slug'] = $data['slug'] ?: Str::slug($data['nombre']);

        $categoria->update($data);

        return redirect()->route('admin.categorias.index')
            ->with('success', 'Categoría actualizada correctamente.');
    }

    public function destroy(CategoriaBlog $categoria)
    {
        // Los artículos quedan con categoria_blog_id = null (nullOnDelete en FK)
        $categoria->delete();

        return redirect()->route('admin.categorias.index')
            ->with('success', 'Categoría eliminada. Los artículos vinculados quedan sin categoría.');
    }

    private function validar(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'nombre'           => ['required', 'string', 'max:100'],
            'slug'             => ['nullable', 'string', 'max:120', 'unique:categorias_blog,slug,' . $ignoreId],
            'descripcion'      => ['nullable', 'string'],
            'meta_title'       => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:320'],
            'og_image'         => ['nullable', 'string', 'max:255'],
        ]);
    }
}
