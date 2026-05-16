<?php

namespace App\Http\Controllers;

use App\Mail\ConfirmacionSuscripcion;
use App\Models\Suscriptor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SuscriptorController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'email'  => 'required|email|max:180',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'email.required'  => 'El email es obligatorio.',
            'email.email'     => 'Introduce un email válido.',
        ]);

        $email    = strtolower(trim($request->email));
        $existente = Suscriptor::where('email', $email)->first();

        if ($existente) {
            if ($existente->confirmado) {
                return back()->with('newsletter_status', 'ya_suscrito');
            }
            try {
                Mail::to($existente->email)->send(new ConfirmacionSuscripcion($existente));
            } catch (\Throwable $e) {
                report($e);
            }
            return back()->with('newsletter_status', 'pendiente');
        }

        $suscriptor = Suscriptor::create([
            'nombre'               => trim($request->nombre),
            'email'                => $email,
            'fuente'               => $request->fuente ?? 'blog-newsletter',
            'token_confirmacion'   => Str::random(48),
            'confirmado'           => false,
        ]);

        try {
            Mail::to($suscriptor->email)->send(new ConfirmacionSuscripcion($suscriptor));
        } catch (\Throwable $e) {
            report($e);
        }

        return back()->with('newsletter_status', 'pendiente');
    }

    public function confirmar(string $token)
    {
        $suscriptor = Suscriptor::where('token_confirmacion', $token)
            ->where('confirmado', false)
            ->firstOrFail();

        $suscriptor->update([
            'confirmado'   => true,
            'confirmed_at' => now(),
        ]);

        return view('newsletter.confirmado', ['nombre' => $suscriptor->nombre]);
    }

    public function cancelar(string $token)
    {
        $suscriptor = Suscriptor::where('token_confirmacion', $token)->firstOrFail();
        $suscriptor->update(['unsubscribed_at' => now()]);

        return view('newsletter.cancelado');
    }
}
