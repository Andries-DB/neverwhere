<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\PinnedGraph;
use App\Models\PinnedTable;
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
            'conversation' => $conversation,
            'pinned_charts' => PinnedGraph::with('message')->where('user_id', auth()->user()->id)->get(),
            'pinned_tables' => PinnedTable::with('message')->where('user_id', auth()->user()->id)->get(),
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
                ['displayAsChart' => false],
                ['thumbs_up' => 0],
                ['thumbs_down' => 0]
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

        $botMessage = $conversation->messages()->create([
            'guid' => (string) Str::uuid(),
            'user_id' => $user->id,
            'message' => $json['output'] ?? '',
            'sql_query' => $json['query'] ?? '',
            'respond_type' => $json['type'] ?? 'text',
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

    public function delete(string $guid)
    {
        $conversation = Conversation::where('guid', $guid)->first();

        abort_unless($conversation, 403, "This source does not exist");

        $conversation->delete();

        return redirect()->route('dashboard');
    }

    public function pinChart(Request $request)
    {
        PinnedGraph::create([
            'user_id' => auth()->id(),
            'message_id' => $request->messageId,
            'title' => $request->title ?? null,
            'sort_chart' => $request->type,
            '_x' => $request->xAxis,
            '_y' => $request->yAxis,
            '_agg' => $request->aggregation,
            ...(isset($request->data) ? ['json' => $request->data] : []),
        ]);

        return [
            'success' => true,
            'message' => 'Chart pinned successfully.',
        ];
    }

    public function unpinChart($id, Request $request)
    {
        $pinned_graph = PinnedGraph::find($id);

        abort_unless($pinned_graph, 403, "This pinned graph does not exist");

        $pinned_graph->delete();

        return redirect()->route('dashboard');
    }

    public function pinTable(Request $request)
    {
        // dd($request->all());
        PinnedTable::create([
            'user_id' => auth()->id(),
            'message_id' => $request->messageId,
            ...(isset($request->data) ? ['json' => $request->data] : []),
        ]);

        return [
            'success' => true,
            'message' => 'Chart pinned successfully.',
        ];
    }

    public function unpinTable($id, Request $request)
    {
        $pinned_table = PinnedTable::find($id);

        abort_unless($pinned_table, 403, "This pinned graph does not exist");

        $pinned_table->delete();

        return redirect()->route('dashboard');
    }

    public function unpinTableByMessage($id, Request $request)
    {
        $pinned_table = PinnedTable::where('message_id', $id)
            ->where('user_id', auth()->user()->id)
            ->first();

        abort_unless($pinned_table, 403, "This pinned graph does not exist");

        $pinned_table->delete();

        return [
            'success' => true,
            'message' => 'Chart unpinned successfully.',
        ];
    }

    public function unpinChartByMessage($id, Request $request)
    {

        $pinned_graph = PinnedGraph::where('message_id', $id)
            ->where('user_id', auth()->user()->id)
            ->first();

        abort_unless($pinned_graph, 403, "This pinned graph does not exist");

        $pinned_graph->delete();

        return [
            'success' => true,
            'message' => 'Chart unpinned successfully.',
        ];
    }

    public function updateChartTitle($id, Request $request)
    {
        $pinned_graph = PinnedGraph::find($id);

        abort_unless($pinned_graph, 403, "This pinned graph does not exist");

        $pinned_graph->title = $request->title;
        $pinned_graph->save();

        return redirect()->back();
    }

    public function likeMessage(Request $request)
    {
        $message_id = $request->input('messageId');

        $message = Message::find($message_id);

        if (!$message) {
            return response()->json(['error' => 'Message not found'], 404);
        }

        // Put thumbs up on true, thumbs down on false
        $message->thumbs_up = true;
        $message->thumbs_down = false;
        $message->save();

        return [
            'success' => true,
            'message' => 'Chart pinned successfully.',
        ];
    }

    public function dislikeMessage(Request $request)
    {
        $message_id = $request->input('messageId');

        $message = Message::find($message_id);

        if (!$message) {
            return response()->json(['error' => 'Message not found'], 404);
        }

        // Put thumbs up on true, thumbs down on false
        $message->thumbs_up = false;
        $message->thumbs_down = true;
        $message->save();

        return [
            'success' => true,
            'message' => 'Chart pinned successfully.',
        ];
    }
}
