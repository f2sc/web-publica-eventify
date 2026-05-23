<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Suscriptor;
use Illuminate\Http\Request;

class SuscriptorAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = Suscriptor::query()->latest();

        if ($request->filled('q')) {
            $query->where(function ($q) use ($request) {
                $q->where('nombre', 'like', '%' . $request->q . '%')
                  ->orWhere('email', 'like', '%' . $request->q . '%');
            });
        }

        if ($request->filled('estado')) {
            if ($request->estado === 'confirmado') {
                $query->where('confirmado', true)->whereNull('unsubscribed_at');
            } elseif ($request->estado === 'pendiente') {
                $query->where('confirmado', false)->whereNull('unsubscribed_at');
            } elseif ($request->estado === 'baja') {
                $query->whereNotNull('unsubscribed_at');
            }
        }

        $suscriptores = $query->paginate(30)->withQueryString();

        $totales = [
            'total'      => Suscriptor::count(),
            'confirmados' => Suscriptor::where('confirmado', true)->whereNull('unsubscribed_at')->count(),
            'pendientes'  => Suscriptor::where('confirmado', false)->whereNull('unsubscribed_at')->count(),
            'bajas'       => Suscriptor::whereNotNull('unsubscribed_at')->count(),
        ];

        return view('admin.suscriptores.index', compact('suscriptores', 'totales'));
    }

    public function destroy(Suscriptor $suscriptor)
    {
        $suscriptor->delete();
        return back()->with('success', 'Suscriptor eliminado.');
    }
}
