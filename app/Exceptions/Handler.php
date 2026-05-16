<?php

namespace App\Exceptions;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Log;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $levels = [];

    protected $dontReport = [];

    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->renderable(function (ModelNotFoundException $e) {
            return response()->view('errors.404', [], 404);
        });

        $this->renderable(function (ApiException $e) {
            return response()->view('errors.503', ['message' => $e->getMessage()], 503);
        });
    }

    public function report(Throwable $e): void
    {
        if ($this->shouldReport($e)) {
            Log::error(sprintf(
                '[www] %s — %s',
                class_basename($e),
                $e->getMessage()
            ), [
                'url'    => request()->fullUrl(),
                'method' => request()->method(),
                'file'   => $e->getFile() . ':' . $e->getLine(),
            ]);
        }

        parent::report($e);
    }
}
