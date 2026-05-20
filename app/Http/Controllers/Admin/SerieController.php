<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Serie;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SerieController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Serie::with('categoriaBlog')->orderBy('nombre')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nombre'            => ['required', 'string', 'max:255'],
            'slug'              => ['nullable', 'string', 'max:255'],
            'descripcion'       => ['nullable', 'string'],
            'categoria_blog_id' => ['nullable', 'integer', 'exists:categorias_blog,id'],
        ]);
        $data['slug'] = ($data['slug'] ?? null) ?: Str::slug($data['nombre']);
        $serie = Serie::create($data);
        return response()->json(['ok' => true, 'serie' => $serie], 201);
    }

    public function update(Request $request, Serie $serie): JsonResponse
    {
        $data = $request->validate([
            'nombre'            => ['required', 'string', 'max:255'],
            'slug'              => ['nullable', 'string', 'max:255'],
            'descripcion'       => ['nullable', 'string'],
            'categoria_blog_id' => ['nullable', 'integer', 'exists:categorias_blog,id'],
        ]);
        $data['slug'] = ($data['slug'] ?? null) ?: Str::slug($data['nombre']);
        $serie->update($data);
        return response()->json(['ok' => true, 'serie' => $serie->fresh()]);
    }

    public function destroy(Serie $serie): JsonResponse
    {
        $serie->delete();
        return response()->json(['ok' => true]);
    }
}
