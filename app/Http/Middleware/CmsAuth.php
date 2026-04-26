<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CmsAuth
{
    public function handle(Request $request, Closure $next)
    {
        if (! session()->has('cms_token')) {
            return redirect()->route('admin.login');
        }

        return $next($request);
    }
}
