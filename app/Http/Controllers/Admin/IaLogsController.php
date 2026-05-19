<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiGeneration;

class IaLogsController extends Controller
{
    public function index()
    {
        $logs = AiGeneration::with('articulo')
            ->orderByDesc('created_at')
            ->paginate(50);

        $totalMes   = AiGeneration::whereMonth('created_at', now()->month)
                        ->whereYear('created_at', now()->year)
                        ->sum('cost_usd');
        $totalSemana = AiGeneration::where('created_at', '>=', now()->startOfWeek())
                        ->sum('cost_usd');
        $totalGeneraciones = AiGeneration::count();

        return view('admin.ia.logs', compact('logs', 'totalMes', 'totalSemana', 'totalGeneraciones'));
    }
}
