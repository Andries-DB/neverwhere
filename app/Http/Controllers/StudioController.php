<?php

namespace App\Http\Controllers;

use App\Models\Knowledge;
use App\Models\Source;
use App\Models\Suggestion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class StudioController extends Controller
{
    public function get()
    {
        $user = Auth::user()->load('sources', 'sources.suggestions', 'sources.knowledge');

        return Inertia::render('Studio/Index', [
            'sources' => $user->sources
        ]);
    }

    public function patch(Request $request)
    {
        $user = User::with('companies')->find($request->user()->id);

        $validated = $request->validate([
            'source_id' => [
                'required',
                Rule::exists('user_source', 'source_id')
                    ->where(fn($query) => $query->where('user_id', $user->id)),
            ],
            'model' => ['string'],
        ]);

        $source = Source::findOrFail($validated['source_id']);
        $source->update(['model' => $validated['model']]);

        $response = Http::timeout(60)->post($source->webhook, [
            'type' => 'model',
            'input' => $source->model,
            'source' => [
                'id' => $source->guid,
                'name' => $source->name,
            ],
            'user' => [
                'id' => $user->guid,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'company' => [
                'id' => $user->companies[0]->guid,
                'name' => $user->companies[0]->company
            ],
            'query' => '',
        ]);

        if ($response->failed()) {
            return redirect()->back()->withErrors(['bot' => 'Er is een fout opgetreden bij het genereren van een bericht.']);
        }

        return back();
    }

    public function patch_suggestion(Request $request)
    {
        $user = User::with('companies')->find($request->user()->id);
        $data = $request->validate([
            'question' => 'required|string',
            'id' => 'nullable|integer',
            'source_id' => 'required|integer|exists:sources,id',
        ], [
            'question.required' => 'Het veld vraag is verplicht.',
        ], [
            'question' => 'vraag',
            'source_id' => 'bron',
        ]);

        $db_id = $data['id'] ?? null;
        $source = Source::findOrFail($data['source_id']);
        $type = "";
        if (empty($db_id)) {
            $type = 'create';

            $suggestion = Suggestion::create([
                'question'   => $data['question'],
                'source_id'  => $data['source_id'],
                'user_id'    => $user->id,
            ]);
        } else {
            $type = 'update';
            $suggestion = Suggestion::where('id', $db_id)
                ->where('source_id', $data['source_id'])
                ->where('user_id', $user->id)
                ->firstOrFail();

            // Updaten
            $suggestion->update([
                'question' => $data['question'],
            ]);
        }

        $response = Http::timeout(60)->post($source->webhook, [
            'type' => 'suggestions',
            'input' => json_encode(
                ['type' => $type, 'question' => $suggestion->question, 'created_at' => $suggestion->created_at, 'updated_at' => $suggestion->updated_at, 'id' => $suggestion->id]
            ),
            'source' => [
                'id' => $source->guid,
                'name' => $source->name,
            ],
            'user' => [
                'id' => $user->guid,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'company' => [
                'id' => $user->companies[0]->guid,
                'name' => $user->companies[0]->company
            ],
            'query' => '',

        ]);

        if ($response->failed()) {
            return redirect()->back()->withErrors(['bot' => 'Er is een fout opgetreden bij het genereren van een bericht.']);
        }

        return $suggestion;
    }


    public function patch_knowledge(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'key' => 'required|string',
            'description' => 'required|string',
            'id' => 'nullable|integer',
            'source_id' => 'required|integer|exists:sources,id',
        ], [
            'key.required' => 'Het key veld is verplicht.',
            'description.required' => 'Het omschrijving veld is verplicht.',
        ]);

        $db_id = $data['id'] ?? null;
        $source = Source::findOrFail($data['source_id']);
        $type = "";

        if (empty($db_id)) {
            $type = 'create';

            $knowledge = Knowledge::create([
                'key'   => $data['key'],
                'description'   => $data['description'],
                'query' => $request['query'],
                'source_id'  => $data['source_id'],
                'user_id'    => $user->id,
            ]);
        } else {
            $type = 'update';
            $knowledge = Knowledge::where('id', $db_id)
                ->where('source_id', $data['source_id'])
                ->where('user_id', $user->id)
                ->firstOrFail();

            $knowledge->update([
                'key' => $data['key'],
                'description' => $data['description'],
                'query' => $request['query']
            ]);
        }

        $response = Http::timeout(60)->post($source->webhook, [
            'type' => 'knowledge',
            'input' => json_encode(
                ['type' => $type, 'key' => $knowledge->key, 'description' => $knowledge->description, 'query' => $knowledge->query, 'created_at' => $knowledge->created_at, 'updated_at' => $knowledge->updated_at, 'id' => $knowledge->id]
            ),
            'source' => [
                'id' => $source->guid,
                'name' => $source->name,
            ],
            'user' => [
                'id' => $user->guid,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'company' => [
                'id' => $user->companies[0]->guid,
                'name' => $user->companies[0]->company
            ],
            'query' => '',

        ]);

        if ($response->failed()) {
            return redirect()->back()->withErrors(['bot' => 'Er is een fout opgetreden bij het opslaan van de knowledge.']);
        }

        return $knowledge;
    }

    public function delete_suggestion(Request $request, $id)
    {
        $user = User::with('companies')->find($request->user()->id);
        $source = Source::findOrFail($request['source_id']);
        $suggestion = Suggestion::where('id', $id)->first();

        $response = Http::timeout(60)->post($source->webhook, [
            'type' => 'suggestions',
            'input' => json_encode(array_merge(
                $suggestion->toArray(),
                ['type' => 'delete']
            )),
            'source' => [
                'id' => $source->guid,
                'name' => $source->name,
            ],
            'user' => [
                'id' => $user->guid,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'company' => [
                'id' => $user->companies[0]->guid,
                'name' => $user->companies[0]->company
            ],
            'query' => '',

        ]);

        if ($response->failed()) {
            return redirect()->back()->withErrors(['bot' => 'Er is een fout opgetreden bij het genereren van een bericht.']);
        }

        $id = $suggestion->id;
        $suggestion->delete();

        return $id;
    }

    public function delete_knowledge($id, Request $request)
    {
        $user = User::with('companies')->find($request->user()->id);
        $source = Source::findOrFail($request['source_id']);
        $knowledge = Knowledge::where('id', $id)->first();

        $id = $knowledge->id;

        $response = Http::timeout(60)->post($source->webhook, [
            'type' => 'knowledge',
            'input' => json_encode(array_merge(
                $knowledge->toArray(),
                ['type' => 'delete']
            )),
            'source' => [
                'id' => $source->guid,
                'name' => $source->name,
            ],
            'user' => [
                'id' => $user->guid,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'company' => [
                'id' => $user->companies[0]->guid,
                'name' => $user->companies[0]->company
            ],
            'query' => '',

        ]);

        if ($response->failed()) {
            return redirect()->back()->withErrors(['bot' => 'Er is een fout opgetreden bij het genereren van een bericht.']);
        }

        $knowledge->delete();

        return $id;
    }
}
