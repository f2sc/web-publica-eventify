<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CmsAuth
{
    public function handle(Request $request, Closure $next)
    {
        if (! session()->has('cms_token')) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['ok' => false, 'message' => 'Sesión expirada. Recarga la página e inicia sesión.'], 401);
            }
            return redirect()->route('admin.login');
        }

        return $next($request);
    }
}
