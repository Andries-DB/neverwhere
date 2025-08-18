<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Dashboard;
use App\Models\Message;
use App\Models\PinnedGraph;
use App\Models\PinnedItem;
use App\Models\PinnedTable;
use App\Models\Source;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;
use Illuminate\Support\Str;

class ConversationController extends Controller
{
    public function read($guid, Request $request)
    {
        $conversation = Conversation::where('guid', $guid)->with('messages', 'user', 'user.sources')->where('visible', 1)->first();

        abort_unless($conversation, 403, 'This conversation does not exist');

        return Inertia::render('Conversations/Read', [
            'conversation' => $conversation,
            'pinned_charts' => PinnedGraph::with('message')->where('user_id', auth()->user()->id)->get(),
            'pinned_tables' => PinnedTable::with('message')->where('user_id', auth()->user()->id)->get(),
            'pinned_items' => PinnedItem::with('message')->where('user_id', auth()->user()->id)->get(),
            'dashboards' => Dashboard::where('user_id',  auth()->user()->id)->orderBy('default', 'desc')->get(),
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

    public function patch($guid, Request $request)
    {
        $conversation = Conversation::where('guid', $guid)->firstOrFail();

        $conversation->title = $request->title;
        $conversation->save();

        return redirect()->back();
    }

    public function postUserMessage($guid, Request $request)
    {
        // dd($request->all());
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
            'source_id' => $request->input('source')
        ]);

        return [
            'message' => array_merge(
                $message->toArray(),
                ['displayAsTable' => false],
                ['displayAsChart' => false],
                ['thumbs_up' => 0],
                ['thumbs_down' => 0]
            ),
            'csrf_token' => csrf_token(),
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

        $user = User::with('companies')->find($request->user()->id);

        $response = Http::timeout(60)->post($source->webhook, [
            'chat_id' => $conversation->id,
            'message_id' => $lastUserMessage->id,
            'type' => 'respond',
            'input' => $lastUserMessage->message,
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
            ],
            'company' => [
                'id' => $user->companies[0]->id,
                'name' => $user->companies[0]->company
            ],
            'source' => [
                'id' => $source->id,
                'name' => $source->name,
            ],
            'query' => '',
            'thumb' => '',
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
            'source_id' => $source->id,
            'send_by' => 'ai',
            ...(isset($json['data'])
                ? ['json' => is_string($json['data']) ? json_decode($json['data'], true) : $json['data']]
                : []
            ),
            'question_message' => $lastUserMessage->id,

        ]);

        return [
            'bot_message' => array_merge(
                $botMessage->toArray(),
                ['displayAsTable' => false, 'displayAsChart' => false]
            ),
            'csrf_token' => csrf_token(),
        ];
    }

    public function delete(string $guid)
    {
        $conversation = Conversation::where('guid', $guid)->first();

        abort_unless($conversation, 403, "This source does not exist");

        $conversation->visible = 0;
        $conversation->save();

        return redirect()->route('dashboard');
    }

    public function pinChart(Request $request)
    {
        $request->validate([
            'dashboard_id' => 'required|exists:dashboards,id',
        ]);

        $dashboard = Dashboard::where('id', $request->dashboard_id)->first();

        PinnedItem::create([
            'user_id' => auth()->id(),
            'message_id' => $request->message['id'],
            'title' => $request->title ?? null,
            'type' => 'graph',
            'sort_chart' => $request->message['selectedChartType'],
            '_x' => $request->message['selectedXAxis'],
            '_y' => $request->message['selectedYAxis'],
            '_agg' => $request->message['selectedAggregation'],
            '_order' => $request->message['selectedSortOrder'],
            'width' => $request->width,
            ...(isset($request->message['json']) ? ['json' => $request->message['json']] : []),

            'display_order' => (PinnedItem::where('user_id', auth()->id())->max('display_order') ?? 0) + 1,
            'dashboard_id' => $dashboard->id,
        ]);


        return;
    }

    public function unpinItem($id)
    {
        $pinned_item = PinnedItem::find($id);

        abort_unless($pinned_item, 403, "This pinned item does not exist");

        $pinned_item->delete();

        return redirect()->route('dashboard');
    }

    public function pinTable(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'dashboard_id' => 'required|exists:dashboards,id',
        ]);
        $dashboard = Dashboard::where('id', $request->dashboard_id)->first();

        PinnedItem::create([
            'user_id' => auth()->id(),
            'message_id' => $request->message['id'],
            'title' => $request->title ?? null,
            'type' => 'table',
            'width' => $request->width,
            ...(isset($request->message['json']) ? ['json' => $request->message['json']] : []),
            'col_def' => $request->message['col_def'],
            'display_order' => (PinnedItem::where('user_id', auth()->id())->max('display_order') ?? 0) + 1,
            'dashboard_id' => $dashboard->id,

        ]);

        return;
    }


    public function updateItemTitle($id, Request $request)
    {
        $pinned_item = PinnedItem::find($id);

        abort_unless($pinned_item, 403, "This pinned item does not exist");

        $pinned_item->title = $request->title;
        $pinned_item->save();

        return redirect()->back();
    }

    public function updateItemWidth($id, Request $request)
    {
        $pinned_item = PinnedItem::find($id);

        abort_unless($pinned_item, 403, "This pinned item does not exist");

        $pinned_item->width = $request->input('width');
        $pinned_item->save();

        return;
    }


    public function updateItemJson($id, Request $request)
    {
        $pinnedItem = PinnedItem::with(['message.source', 'message.conversation'])->find($id);

        abort_if(!$pinnedItem, 403, 'This pinned item does not exist');

        $user = auth()->user()->loadMissing('companies');

        $message = $pinnedItem->message;
        $company = $user->companies->first();

        $response = Http::timeout(60)->post($message->source->webhook, [
            'chat_id' => $message->conversation->id,
            'message_id' => $message->question_message,
            'type' => 'refresh',
            'input' => $request->input('feedback'),
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
            ],
            'company' => [
                'id' => $company->id,
                'name' => $company->company,
            ],
            'source' => [
                'id' => $message->source->id,
                'name' => $message->source->name,
            ],
            'query' => $message->sql_query,
        ]);

        if ($response->failed()) {
            return redirect()->back()->withErrors(['bot' => 'Er is een fout opgetreden bij het genereren van een bericht.']);
        }

        $json = $response->json();

        $pinnedItem->update([
            ...(isset($json['data'])
                ? ['json' => is_string($json['data']) ? json_decode($json['data'], true) : $json['data']]
                : []
            ),
        ]);

        return;
    }


    public function duplicateItem($id, Request $request)
    {
        $pinnedItem = PinnedItem::find($id);

        abort_if(!$pinnedItem, 403, 'This pinned graph does not exist');

        $newGraph = $pinnedItem->replicate();
        $newGraph->created_at = now();
        $newGraph->updated_at = now();
        $newGraph->save();

        return;
    }

    public function likeMessage(Request $request)
    {
        $message = Message::with('conversation', 'source')->find($request->input('message_id'));

        if (!$message) {
            return response()->json(['error' => 'Message not found'], 404);
        }

        $message->update([
            'thumbs_up' => true,
            'thumbs_down' => false,
            'feedback' => $request->input('feedback', null),
        ]);

        $user = auth()->user()->load('companies');

        if ($user->companies->isEmpty()) {
            return response()->json(['error' => 'User has no associated company'], 400);
        }

        $response = Http::timeout(60)->post($message->source->webhook, [
            'chat_id' => $message->conversation->id,
            'message_id' => $message->question_message,
            'type' => 'thumb_up',
            'input' => $request->input('feedback', null),
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
            ],
            'company' => [
                'id' => $user->companies[0]->id,
                'name' => $user->companies[0]->company
            ],
            'source' => [
                'id' => $message->source->id,
                'name' => $message->source->name,
            ],
            'query' => $message->sql_query,

        ]);

        if ($response->failed()) {
            return response()->json(['error' => 'Er is een fout opgetreden bij disliken van het bericht.'], 500);
        }

        return;
    }

    public function dislikeMessage(Request $request)
    {
        $message = Message::with('conversation', 'source')->find($request->input('message_id'));

        if (!$message) {
            return response()->json(['error' => 'Message not found'], 404);
        }

        // Put thumbs up on true, thumbs down on false
        $message->update([
            'thumbs_up' => false,
            'thumbs_down' => true,
            'feedback' => $request->input('feedback', null),
        ]);

        $user = auth()->user()->load('companies');

        if ($user->companies->isEmpty()) {
            return response()->json(['error' => 'User has no associated company'], 400);
        }

        $response = Http::timeout(60)->post($message->source->webhook, [
            'chat_id' => $message->conversation->id,
            'message_id' => $message->question_message,
            'type' => 'thumb_down',
            'input' => $request->input('feedback', null),
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
            ],
            'company' => [
                'id' => $user->companies[0]->id,
                'name' => $user->companies[0]->company
            ],
            'source' => [
                'id' => $message->source->id,
                'name' => $message->source->name,
            ],
            'query' => $message->sql_query,

        ]);

        if ($response->failed()) {
            return response()->json(['error' => 'Er is een fout opgetreden bij het liken van het bericht.'], 500);
        }

        return;
    }

    public function summarize(Request $request)
    {
        $message = Message::with('conversation', 'source')->find($request->input('message_id'));

        if (!$message) {
            return response()->json(['error' => 'Message not found'], 404);
        }

        $user = auth()->user()->load('companies');

        $response = Http::timeout(60)->post($message->source->webhook, [
            'chat_id' => $message->conversation->id,
            'message_id' => $message->question_message,
            'type' => 'summarize',
            'input' => '',
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
            ],
            'company' => [
                'id' => $user->companies[0]->id,
                'name' => $user->companies[0]->company
            ],
            'source' => [
                'id' => $message->source->id,
                'name' => $message->source->name,
            ],
            'query' => $message->sql_query,
        ]);

        if ($response->failed()) {
            return redirect()->back()->withErrors(['bot' => 'Er is een fout opgetreden bij het genereren van een bericht.']);
        }

        $json = $response->json();
        $output = $json['output'] ?? 'Geen samenvatting beschikbaar. Dr. Itchy heeft even pauze.';

        return [
            'summary' => $output,
            'csrf_token' => csrf_token(),
        ];
    }

    public function saveColDef(Request $request)
    {
        $message = Message::where('guid', $request->input('message_guid'))->first();

        if (!$message) {
            return response()->json(['error' => 'Message not found'], 404);
        }

        $message->col_def = $request->input('data');
        $message->save();

        return [
            'summary' => $request->input('data'),
            'csrf_token' => csrf_token(),
        ];
    }

    public function saveChartDef(Request $request)
    {
        // dd($request->all());
        $message = Message::where('guid', $request->input('message_guid'))->first();


        if (!$message) {
            return response()->json(['error' => 'Message not found'], 404);
        }

        $message->_agg = $request->input('data')['_agg'];
        $message->_sort = $request->input('data')['_sort'];
        $message->_x = $request->input('data')['_x'];
        $message->_y = $request->input('data')['_y'];
        $message->_order = $request->input('data')['_order'];
        $message->save();

        return [
            'summary' => $request->input('data'),
            'csrf_token' => csrf_token(),
        ];
    }
}
