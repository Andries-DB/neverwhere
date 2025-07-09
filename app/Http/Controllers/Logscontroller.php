<?php

namespace App\Http\Controllers;

use App\Models\Log;
use Illuminate\Http\Request;
use Inertia\Inertia;

class Logscontroller extends Controller
{
    public function index()
    {
        $logs = Log::with('user')->orderByDesc('created_at')->get()->map(function ($log) {
            return [
                'id' => $log->id,
                'message' => $log->message,
                'user' => $log->user,
                'created_at' => $log->created_at->format('d/m/Y '),
            ];
        });

        return Inertia::render('Logs/Index', [
            'logs' =>  $logs,
        ]);
    }
}
