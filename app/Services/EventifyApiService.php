<?php

namespace App\Services;

use App\Exceptions\ApiException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class EventifyApiService
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.eventify.api_url'), '/');
    }

    public function comercios(array $filters = []): array
    {
        $key = 'api_comercios_' . md5(serialize($filters));
        return Cache::remember($key, now()->addMinutes(30), function () use ($filters) {
            return $this->get('/comercios', $filters);
        });
    }

    public function comercio(string $slug): array
    {
        return Cache::remember("api_comercio_{$slug}", now()->addMinutes(30), function () use ($slug) {
            return $this->get("/comercios/{$slug}");
        });
    }

    public function localidades(): array
    {
        return Cache::remember('api_localidades', now()->addHours(2), function () {
            return $this->get('/localidades');
        });
    }

    public function localidad(string $slug): array
    {
        return Cache::remember("api_localidad_{$slug}", now()->addHour(), function () use ($slug) {
            return $this->get("/localidades/{$slug}");
        });
    }

    public function categorias(): array
    {
        return Cache::remember('api_categorias', now()->addHours(2), function () {
            return $this->get('/categorias');
        });
    }

    public function asociaciones(): array
    {
        return Cache::remember('api_asociaciones', now()->addHours(2), function () {
            return $this->get('/asociaciones');
        });
    }

    public function asociacion(string $slug): array
    {
        return Cache::remember("api_asociacion_{$slug}", now()->addHour(), function () use ($slug) {
            return $this->get("/asociaciones/{$slug}");
        });
    }

    public function stats(): array
    {
        return Cache::remember('api_stats', now()->addHours(2), function () {
            return $this->get('/stats');
        });
    }

    public function buscar(string $q): array
    {
        return $this->get('/buscar', ['q' => $q]);
    }

    private function get(string $endpoint, array $query = []): array
    {
        $response = Http::timeout(5)
            ->acceptJson()
            ->get($this->baseUrl . $endpoint, $query);

        if ($response->notFound()) {
            throw new ModelNotFoundException("Recurso no encontrado: {$endpoint}");
        }

        if ($response->failed()) {
            throw new ApiException("Error {$response->status()} al llamar a {$endpoint}");
        }

        return $response->json() ?? [];
    }
}
