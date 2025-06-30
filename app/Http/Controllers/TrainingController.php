<?php

namespace App\Http\Controllers;

use App\Models\PinnedGraph;
use App\Models\PinnedTable;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TrainingController extends Controller
{
    public function index()
    {
        $pinned_graphs = PinnedGraph::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->with('message', 'message.conversation')
            ->get();

        // dd($pinned_graphs);

        $pinned_tables = PinnedTable::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->with('message', 'message.conversation')
            ->get();

        return Inertia::render('Dashboard', [
            // 'conversations' => $conversations,
            'pinned_graphs' => $pinned_graphs,
            'pinned_tables' => $pinned_tables,
        ]);
    }
}
