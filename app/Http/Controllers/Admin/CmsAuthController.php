<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CmsAuthController extends Controller
{
    public function showLogin()
    {
        if (session()->has('cms_token')) {
            return redirect()->route('admin.articulos.index');
        }
        return view('admin.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $response = Http::timeout(15)->acceptJson()->post(config('services.eventify.cms_login'), [
            'email'    => $request->email,
            'password' => $request->password,
        ]);

        if ($response->status() === 403) {
            return back()->withErrors(['email' => 'No tienes permisos de administrador.']);
        }

        if ($response->failed()) {
            return back()->withErrors(['email' => 'Credenciales incorrectas.']);
        }

        $data = $response->json();

        if (empty($data['token'])) {
            return back()->withErrors(['email' => 'Respuesta inesperada del servidor.']);
        }

        session([
            'cms_token' => $data['token'],
            'cms_user'  => $data['user'],
        ]);

        return redirect()->route('admin.articulos.index');
    }

    public function logout(Request $request)
    {
        Http::withToken(session('cms_token'))
            ->timeout(5)
            ->post(config('services.eventify.cms_logout'));

        session()->forget(['cms_token', 'cms_user']);

        return redirect()->route('admin.login');
    }
}
