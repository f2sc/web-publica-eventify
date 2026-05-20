<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Articulo;
use App\Models\CategoriaBlog;
use App\Models\Serie;
use App\Services\AI\AiCalendarioService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Throwable;

class CalendarioController extends Controller
{
    public function __construct(private readonly AiCalendarioService $iaService) {}

    public function index()
    {
        $series = Serie::with(['articulos' => function ($q) {
            $q->whereNull('fecha_publicacion')
              ->whereIn('estado', ['borrador', 'programado'])
              ->orderBy('orden_en_serie');
        }])->get()->filter(fn ($s) => $s->articulos->isNotEmpty())->values();

        $sueltos = Articulo::whereNull('fecha_publicacion')
            ->whereNull('serie_id')
            ->whereIn('estado', ['borrador', 'programado'])
            ->orderByDesc('created_at')
            ->get();

        $avisos = Articulo::whereIn('estado', ['programado', 'borrador'])
            ->whereNotNull('fecha_publicacion')
            ->whereBetween('fecha_publicacion', [now(), now()->addDays(7)])
            ->where(fn ($q) => $q->whereNull('contenido')->orWhere('contenido', ''))
            ->orderBy('fecha_publicacion')
            ->get();

        $categorias = CategoriaBlog::orderBy('nombre')->get();

        return view('admin.calendario.index', compact('series', 'sueltos', 'avisos', 'categorias'));
    }

    public function events(Request $request): JsonResponse
    {
        $year  = (int) $request->query('year',  now()->year);
        $month = (int) $request->query('month', now()->month);

        $articulos = Articulo::whereYear('fecha_publicacion', $year)
            ->whereMonth('fecha_publicacion', $month)
            ->whereNotNull('fecha_publicacion')
            ->whereIn('estado', ['borrador', 'programado', 'publicado'])
            ->select('id', 'titulo', 'slug', 'estado', 'fecha_publicacion', 'serie_id', 'contenido')
            ->get()
            ->map(fn ($a) => [
                'id'               => $a->id,
                'titulo'           => $a->titulo,
                'slug'             => $a->slug,
                'estado'           => $a->estado,
                'fecha_publicacion'=> $a->fecha_publicacion->format('Y-m-d H:i'),
                'serie_id'         => $a->serie_id,
                'contenido_vacio'  => empty($a->contenido),
            ]);

        return response()->json($articulos);
    }

    public function programarSerie(Request $request): JsonResponse
    {
        $request->validate([
            'serie_id'       => ['required', 'exists:series,id'],
            'start_datetime' => ['required', 'date_format:Y-m-d H:i'],
            'cadencia'       => ['required', 'in:xdias,semana,xsemanas'],
            'cada_x_dias'    => ['required_if:cadencia,xdias', 'integer', 'min:1'],
            'dia_semana'     => ['required_if:cadencia,semana', 'integer', 'between:0,6'],
            'cada_x_semanas' => ['required_if:cadencia,xsemanas', 'integer', 'min:1'],
        ]);

        $articulos  = Articulo::where('serie_id', $request->serie_id)->orderBy('orden_en_serie')->get();
        $startDate  = Carbon::createFromFormat('Y-m-d H:i', $request->start_datetime);
        $fechas     = [];
        $prev       = null;

        foreach ($articulos as $i => $articulo) {
            $fecha = match (true) {
                $i === 0                        => $startDate->copy(),
                $request->cadencia === 'xdias'  => $startDate->copy()->addDays($i * (int) $request->cada_x_dias),
                $request->cadencia === 'semana' => $this->nextWeekday($prev, (int) $request->dia_semana),
                default                         => $startDate->copy()->addWeeks($i * (int) $request->cada_x_semanas),
            };
            $prev = $fecha->copy();
            $articulo->update(['fecha_publicacion' => $fecha]);
            $fechas[] = ['id' => $articulo->id, 'titulo' => $articulo->titulo, 'fecha' => $fecha->format('Y-m-d H:i')];
        }

        return response()->json(['ok' => true, 'fechas' => $fechas]);
    }

    public function iaIdeas(Request $request): JsonResponse
    {
        $data = $request->validate([
            'descripcion'       => ['required', 'string', 'max:2000'],
            'categoria_blog_id' => ['nullable', 'integer'],
        ]);
        try {
            $ideas = $this->iaService->generateIdeas($data['descripcion'], $data['categoria_blog_id'] ?? null);
            return response()->json(['ok' => true, 'ideas' => $ideas]);
        } catch (Throwable $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function iaPlan(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nombre'       => ['required', 'string', 'max:255'],
            'descripcion'  => ['required', 'string', 'max:2000'],
            'n_articulos'  => ['required', 'integer', 'min:2', 'max:20'],
        ]);
        try {
            $plan = $this->iaService->generateSeriePlan($data['nombre'], $data['descripcion'], $data['n_articulos']);
            return response()->json(['ok' => true, 'plan' => $plan]);
        } catch (Throwable $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function crearArticuloTintero(Request $request): JsonResponse
    {
        $data = $request->validate([
            'titulo'            => ['required', 'string', 'max:255'],
            'categoria_blog_id' => ['nullable', 'integer', 'exists:categorias_blog,id'],
        ]);

        $articulo = Articulo::create([
            'titulo'            => $data['titulo'],
            'slug'              => Str::slug($data['titulo']) . '-' . substr(uniqid(), -4),
            'estado'            => 'borrador',
            'schema_type'       => 'BlogPosting',
            'categoria_blog_id' => $data['categoria_blog_id'] ?? null,
            'enviar_newsletter' => true,
        ]);

        return response()->json(['ok' => true, 'articulo' => $articulo], 201);
    }

    public function crearSerieTintero(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nombre'             => ['required', 'string', 'max:255'],
            'categoria_blog_id'  => ['nullable', 'integer', 'exists:categorias_blog,id'],
            'articulos'          => ['required', 'array', 'min:2'],
            'articulos.*.titulo' => ['required', 'string', 'max:255'],
        ]);

        $serie = Serie::create([
            'nombre'            => $data['nombre'],
            'slug'              => Str::slug($data['nombre']),
            'categoria_blog_id' => $data['categoria_blog_id'] ?? null,
        ]);

        $creados = [];
        foreach ($data['articulos'] as $i => $item) {
            $creados[] = Articulo::create([
                'titulo'            => $item['titulo'],
                'slug'              => Str::slug($item['titulo']) . '-' . substr(uniqid(), -4),
                'estado'            => 'borrador',
                'schema_type'       => 'BlogPosting',
                'serie_id'          => $serie->id,
                'orden_en_serie'    => $i + 1,
                'categoria_blog_id' => $data['categoria_blog_id'] ?? null,
                'enviar_newsletter' => true,
            ]);
        }

        return response()->json(['ok' => true, 'serie' => $serie, 'articulos' => $creados], 201);
    }

    private function nextWeekday(Carbon $after, int $dayOfWeek): Carbon
    {
        $date = $after->copy()->addDay();
        while ($date->dayOfWeek !== $dayOfWeek) {
            $date->addDay();
        }
        return $date;
    }
}
