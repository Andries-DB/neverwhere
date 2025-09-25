<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Dashboard;
use App\Models\Message;
use App\Models\PinnedItem;
use App\Models\Source;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;
use Illuminate\Support\Str;

class ConversationController extends Controller
{
    public function read($guid, Request $request)
    {
        $conversation = Conversation::where('guid', $guid)->with('messages', 'user.sources.suggestions')->where('visible', 1)->first();
        abort_unless($conversation, 403, 'This conversation does not exist');

        return Inertia::render('Conversations/Read', [
            'conversation' => $conversation,
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
            'chat_id' => $conversation->guid,
            'message_id' => $lastUserMessage->id,
            'type' => 'respond',
            'input' => $lastUserMessage->message,
            'user' => [
                'id' => $user->guid,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'company' => [
                'id' => $user->companies[0]->guid,
                'name' => $user->companies[0]->company
            ],
            'source' => [
                // 'id' => 'df3e7a24-d6a9-4188-8852-70628d16edda',

                'id' => $source->guid,
                'name' => $source->name,
            ],
            'query' => '',

        ]);


        if ($response->failed()) {
            return redirect()->back()->withErrors(['bot' => 'Er is een fout opgetreden bij het genereren van een bericht.']);
        }

        $json = $response->json();
        $graph = $json['graph'] ?? null;
        $graphConfig = null;

        if ($graph) {
            $graphConfig = [
                "Graph" =>  is_string($graph) ? json_decode($graph, true) : $graph,
            ];
        }


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
            'config' => $graphConfig ? json_encode($graphConfig) : null,
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
            '_order' => $request->message['selectedSortField'],
            '_order_dir' => $request->message['selectedSortDirection'],
            'width' => $request->width,
            ...(isset($request->message['json']) ? ['json' => $request->message['json']] : []),
            'display_order' => (PinnedItem::where('user_id', auth()->id())->max('display_order') ?? 0) + 1,
            'dashboard_id' => $dashboard->id,
            'last_updated' => Carbon::parse($request->message['updated_at'])->format('Y-m-d H:i:s'),
            'config' => $request->message['config'],
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
        $request->validate([
            'dashboard_id' => 'nullable|exists:dashboards,id',
            'new_dashboard_name' => 'nullable|string|max:255',
        ]);

        if ($request->dashboard_id) {
            $dashboard = Dashboard::findOrFail($request->dashboard_id);
        } elseif ($request->new_dashboard_name) {
            $dashboard = Dashboard::create([
                'guid' => (string) Str::uuid(),

                'name' => $request->new_dashboard_name,
                'user_id' => auth()->id(), // optioneel: eigenaar van het dashboard
            ]);
        } else {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'dashboard_id' => ['Je moet een bestaand dashboard selecteren of een nieuwe naam opgeven.'],
            ]);
        }

        $colDef = $request->col_def;
        $integratedChart = null;
        if (isset($colDef['integrated_chart'])) {
            $integratedChart = $colDef['integrated_chart'];
            unset($colDef['integrated_chart']);
        }


        // dd($integratedChart, $colDef);

        PinnedItem::create([
            'user_id' => auth()->id(),
            'message_id' => $request->message['id'],
            'title' => $request->title ?? null,
            'type' => 'table',
            'width' => $request->width,
            ...(isset($request->message['json']) ? ['json' => $request->message['json']] : []),
            'col_def' => $colDef,
            'integrated_chart' => $integratedChart,
            'display_order' => (PinnedItem::where('user_id', auth()->id())->max('display_order') ?? 0) + 1,
            'dashboard_id' => $dashboard->id,
            'last_updated' => now()->format('Y-m-d H:i:s'),
            'total_row' => $request->message['features']['total_row'] ? 1 : 0,
        ]);

        $message = Message::where('id', $request->message['id'])->first();
        $message->col_def = $colDef;
        $message->save();

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
            'chat_id' => $message->conversation->guid,
            'message_id' => $message->question_message,
            'type' => 'refresh',
            'input' => $request->input('feedback'),
            'user' => [
                'id' => $user->guid,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'company' => [
                'id' => $company->guid,
                'name' => $company->company,
            ],
            'source' => [
                'id' => $message->source->guid,
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
            'last_updated' =>  now()->format('Y-m-d H:i:s'),
        ]);

        return;
    }

    public function changeInput($id, Request $request)
    {
        $message = Message::with(['source', 'conversation'])->find($id);

        abort_if(!$message, 403, 'This pinned item does not exist');

        $user = auth()->user()->loadMissing('companies');
        $company = $user->companies->first();
        $response = Http::timeout(60)->post($message->source->webhook, [
            'chat_id' => $message->conversation->guid,
            'message_id' => $message->question_message,
            'type' => 'change',
            'input' => $request->input('feedback'),
            'user' => [
                'id' => $user->guid,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'company' => [
                'id' => $company->guid,
                'name' => $company->company,
            ],
            'source' => [
                // 'id' => 'df3e7a24-d6a9-4188-8852-70628d16edda',
                'id' => $message->source->guid,
                'name' => $message->source->name,
            ],
            'query' => $message->sql_query,
            'settings' => $request->config
        ]);

        if ($response->failed()) {
            return redirect()->back()->withErrors(['bot' => 'Er is een fout opgetreden bij het genereren van een bericht.']);
        }

        $json = $response->json();
        $output = $json['output'];

        $message->config = json_encode($output[0]);
        // $message->save();

        return [
            'message' => $message
        ];
    }


    public function duplicateItem($id)
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
            'chat_id' => $message->conversation->guid,
            'message_id' => $message->question_message,
            'type' => 'thumb_up',
            'input' => $request->input('feedback', null),
            'user' => [
                'id' => $user->guid,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'company' => [
                'id' => $user->companies[0]->guid,
                'name' => $user->companies[0]->company
            ],
            'source' => [
                // 'id' => 'df3e7a24-d6a9-4188-8852-70628d16edda',

                'id' => $message->source->guid,
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
            'chat_id' => $message->conversation->guid,
            'message_id' => $message->question_message,
            'type' => 'thumb_down',
            'input' => $request->input('feedback', null),
            'user' => [
                'id' => $user->guid,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'company' => [
                'id' => $user->companies[0]->guid,
                'name' => $user->companies[0]->company
            ],
            'source' => [
                // 'id' => 'df3e7a24-d6a9-4188-8852-70628d16edda',

                'id' => $message->source->guid,
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
        $conversation = $message->conversation;

        if (!$message) {
            return response()->json(['error' => 'Message not found'], 404);
        }

        $user = auth()->user()->load('companies');
        // dd($request->input('action'));
        $response = Http::timeout(60)->post($message->source->webhook, [
            'chat_id' => $message->conversation->guid,
            'message_id' => $message->question_message,
            'type' => $request->input('action') === 'summarize' ? 'summarize' : 'ask_suggestion',
            'input' => '',
            'user' => [
                'id' => $user->guid,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'company' => [
                'id' => $user->companies[0]->guid,
                'name' => $user->companies[0]->company
            ],
            'source' => [
                // 'id' => 'df3e7a24-d6a9-4188-8852-70628d16edda',

                'id' => $message->source->guid,
                'name' => $message->source->name,
            ],
            'query' => $message->sql_query,
        ]);

        if ($response->failed()) {
            return redirect()->back()->withErrors(['bot' => 'Er is een fout opgetreden bij het genereren van een bericht.']);
        }

        $json = $response->json();
        $output = null;
        if ($request->input('action') === 'suggestion') {
            $output = $conversation->messages()->create([
                'guid' => (string) Str::uuid(),
                'user_id' => $user->id,
                'message' => '',
                'sql_query' => '',
                'respond_type' => 'ask_suggestion',
                'source_id' => $message->source->id,
                'send_by' => 'ai',
                ...(isset($json['output']['ask_suggestion'])
                    ? ['json' => json_encode(array_column($json['output']['ask_suggestion'], 0))]
                    : []
                ),

            ]);
        } else {
            $output = $conversation->messages()->create([
                'guid' => (string) Str::uuid(),
                'user_id' => $user->id,
                'message' => '',
                'sql_query' => '',
                'respond_type' => 'summary',
                'source_id' => $message->source->id,
                'send_by' => 'ai',
                'json' => $json['output'],
            ]);
        }


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
        $message->_total_row = $request->input('total_row') === 'bottom' ? 1 : 0;
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

        $message->_order = $request->input('data')['_order'];
        $message->_order_dir = $request->input('data')['_order_dir'];
        $message->_x = $request->input('data')['_x'];
        $message->_y = $request->input('data')['_y'];
        $message->_agg = $request->input('data')['_agg'];
        $message->_sort = $request->input('data')['_sort'];

        $message->save();

        return [
            'summary' => $request->input('data'),
            'csrf_token' => csrf_token(),
        ];
    }
}
