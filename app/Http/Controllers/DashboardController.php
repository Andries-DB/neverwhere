<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function show()
    {
        $conversations = Conversation::with(['messages' => function ($query) {
            $query->latest()->limit(1); // Alleen het laatste bericht per conversatie
        }])
            ->where('user_id', auth()->user()->id)
            ->latest('updated_at') // Meest recent bijgewerkt
            ->take(3)
            ->get()
            ->map(function ($conversation) {
                $message = $conversation->messages->first();

                return [
                    'id' => $conversation->id,
                    'title' => $conversation->title ?? 'Naamloos gesprek',
                    'lastMessage' => $message ? $message->message : null,
                    'updatedAt' => $message ? $message->created_at->format('Y-m-d H:i') : null,
                    'guid' => $conversation->guid
                ];
            });

        return Inertia::render('Dashboard', [
            'conversations' => $conversations,
        ]);
    }
}
