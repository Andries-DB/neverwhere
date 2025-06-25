<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\PinnedGraph;
use App\Models\PinnedTable;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function show()
    {
        // $conversations = Conversation::with(['messages' => function ($query) {
        //     $query->latest()->limit(1); // Alleen het laatste bericht per conversatie
        // }])
        //     ->where('user_id', auth()->user()->id)
        //     ->latest('updated_at') // Meest recent bijgewerkt
        //     ->take(3)
        //     ->get()
        //     ->map(function ($conversation) {
        //         $message = $conversation->messages->first();

        //         return [
        //             'id' => $conversation->id,
        //             'title' => $conversation->title ?? 'Naamloos gesprek',
        //             'lastMessage' => $message ? $message->message : null,
        //             'updatedAt' => $message ? $message->created_at->format('Y-m-d H:i') : null,
        //             'guid' => $conversation->guid
        //         ];
        //     });

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
