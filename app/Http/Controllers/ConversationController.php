<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Source;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;
use Illuminate\Support\Str;

class ConversationController extends Controller
{
    public function read($guid)
    {
        $conversation = Conversation::where('guid', $guid)->with('messages', 'user', 'user.sources')->firstOrFail();


        return Inertia::render('Conversations/Read', [
            'conversation' => $conversation
        ]);
    }

    public function create(Request $request)
    {
        $conversation = Conversation::create([
            'guid' => (string) Str::uuid(),
            'user_id' => $request->user()->id,
            'status' => 'active'
        ]);

        $guid = $conversation->guid;

        return redirect()->route('conversation.read', $guid);
    }

    public function postUserMessage($guid, Request $request)
    {
        $messageText = $request->input('message');
        $conversation = Conversation::where('guid', $guid)->firstOrFail();

        if (!$conversation->title) {
            $conversation->title = substr($messageText, 0, 25);
            $conversation->save();
        }

        $message = $conversation->messages()->create([
            'guid' => (string) Str::uuid(),
            'user_id' => $request->user()->id,
            'message' => $messageText,
            'send_by' => 'user',
        ]);

        return [
            'message' => array_merge(
                $message->toArray(),
                ['displayAsTable' => false],
                ['displayAsChart' => false]
            )
        ];
    }

    public function getBotResponse(string $guid, Request $request)
    {
        $conversation = Conversation::where('guid', $guid)->firstOrFail();
        $source = Source::find($request->input('source'));

        if (!$source) {
            return redirect()->back()->withErrors(['bot' => 'Ongeldige bron geselecteerd.']);
        }

        $lastUserMessage = $conversation->messages()
            ->where('send_by', 'user')
            ->latest()
            ->first();

        if (!$lastUserMessage) {
            return redirect()->back()->withErrors(['bot' => 'Geen gebruikersbericht gevonden om op te reageren.']);
        }

        $user = $request->user();

        $response = Http::timeout(60)->post($source->webhook, [
            'message_id' => $conversation->id,
            'type' => 'respond',
            'input' => $lastUserMessage->message,
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
            ],
        ]);

        if ($response->failed()) {
            return redirect()->back()->withErrors(['bot' => 'Er is een fout opgetreden bij het genereren van een bericht.']);
        }

        $json = $response->json();
        // dd($json);

        $botMessage = $conversation->messages()->create([
            'guid' => (string) Str::uuid(),
            'user_id' => $user->id,
            'message' => $json['output'] ?? '',
            'send_by' => 'ai',
            ...(isset($json['data']['json']) ? ['json' => json_decode($json['data']['json'], true)] : []),

        ]);

        return [
            'bot_message' => array_merge(
                $botMessage->toArray(),
                ['displayAsTable' => false, 'displayAsChart' => false]
            ),
        ];
    }
}
