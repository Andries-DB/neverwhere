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
        // dd($request->all());
        $user = User::with('companies')->find($request->user()->id);


        $validated = $request->validate([
            'source_id' => [
                'required',
                Rule::exists('user_source', 'source_id')
                    ->where(fn($query) => $query->where('user_id', $user->id)),
            ],
            'model' => ['string'], // eventueel aanpassen
        ]);

        $source = Source::findOrFail($validated['source_id']);
        $source->update(['model' => $validated['model']]);

        return back();
    }

    public function patch_suggestion(Request $request)
    {
        $user = User::with('companies')->find($request->user()->id);
        $data = $request->validate([
            'suggestion.question' => 'required|string',
            'suggestion.db_id' => 'nullable|integer',
            'suggestion.id' => 'required|integer',
            'source_id' => 'required|integer|exists:sources,id',
        ], [
            'suggestion.question.required' => 'Het veld vraag is verplicht.',
        ], [
            'suggestion.question' => 'vraag',
            'source_id' => 'bron',
        ]);


        // db_id ophalen
        $db_id = $data['suggestion']['db_id'] ?? null;
        $source = Source::findOrFail($data['source_id']);

        if (empty($db_id)) {
            $suggestion = Suggestion::create([
                'question'   => $data['suggestion']['question'],
                'source_id'  => $data['source_id'],
                'user_id'    => $user->id,
            ]);
        } else {

            $suggestion = Suggestion::where('id', $db_id)
                ->where('source_id', $data['source_id'])
                ->where('user_id', $user->id)
                ->firstOrFail();

            // Updaten
            $suggestion->update([
                'question' => $data['suggestion']['question'],
            ]);
        }

        $response = Http::timeout(60)->post($source->webhook, [
            'type' => 'suggestions',
            'input' => json_encode(array_merge(
                $data['suggestion'],
                ['type' => 'update']
            )),
            'source' => [
                'id' => $source->id,
                'name' => $source->name,
            ],
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'company' => [
                'id' => $user->companies[0]->id,
                'name' => $user->companies[0]->company
            ],
            'query' => '',

        ]);

        if ($response->failed()) {
            return redirect()->back()->withErrors(['bot' => 'Er is een fout opgetreden bij het genereren van een bericht.']);
        }

        return $suggestion->id;
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
                'id' => $source->id,
                'name' => $source->name,
            ],
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'company' => [
                'id' => $user->companies[0]->id,
                'name' => $user->companies[0]->company
            ],
            'query' => '',

        ]);

        if ($response->failed()) {
            return redirect()->back()->withErrors(['bot' => 'Er is een fout opgetreden bij het genereren van een bericht.']);
        }

        $suggestion->delete();

        return;
    }

    public function patch_knowledge(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'knowledge.key' => 'required|string',
            'knowledge.description' => 'required|string',
            'knowledge.query' => 'required|string',
            'knowledge.db_id' => 'nullable|integer',
            'knowledge.id' => 'required|integer',
            'source_id' => 'required|integer|exists:sources,id',
        ], [
            'knowledge.key.required' => 'Het key veld is verplicht.',
            'knowledge.description.required' => 'Het omschrijving veld is verplicht.',
            'knowledge.query.required' => 'Het query veld is verplicht.',
        ]);

        // db_id ophalen
        $db_id = $data['knowledge']['db_id'] ?? null;
        $source = Source::findOrFail($data['source_id']);


        if (empty($db_id)) {
            $knowledge = Knowledge::create([
                'key'   => $data['knowledge']['key'],
                'description'   => $data['knowledge']['description'],
                'query'   => $data['knowledge']['query'],
                'source_id'  => $data['source_id'],
                'user_id'    => $user->id,
            ]);
        } else {

            $knowledge = Knowledge::where('id', $db_id)
                ->where('source_id', $data['source_id'])
                ->where('user_id', $user->id)
                ->firstOrFail();

            // Updaten
            $knowledge->update([
                'key' => $data['knowledge']['key'],
                'description' => $data['knowledge']['description'],
                'query' => $data['knowledge']['query'],
            ]);
        }


        $response = Http::timeout(60)->post($source->webhook, [
            'type' => 'knowledge',
            'input' => json_encode(array_merge(
                $data['knowledge'],
                ['type' => 'update']
            )),
            'source' => [
                'id' => $source->id,
                'name' => $source->name,
            ],
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'company' => [
                'id' => $user->companies[0]->id,
                'name' => $user->companies[0]->company
            ],
            'query' => '',

        ]);

        if ($response->failed()) {
            return redirect()->back()->withErrors(['bot' => 'Er is een fout opgetreden bij het genereren van een bericht.']);
        }

        return $knowledge->id;
    }

    public function delete_knowledge($id, Request $request)
    {
        $user = User::with('companies')->find($request->user()->id);
        $source = Source::findOrFail($request['source_id']);

        $knowledge = Knowledge::where('id', $id)->first();


        $response = Http::timeout(60)->post($source->webhook, [
            'type' => 'suggestions',
            'input' => json_encode(array_merge(
                $knowledge->toArray(),
                ['type' => 'delete']
            )),
            'source' => [
                'id' => $source->id,
                'name' => $source->name,
            ],
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'company' => [
                'id' => $user->companies[0]->id,
                'name' => $user->companies[0]->company
            ],
            'query' => '',

        ]);

        if ($response->failed()) {
            return redirect()->back()->withErrors(['bot' => 'Er is een fout opgetreden bij het genereren van een bericht.']);
        }

        $knowledge->delete();

        return;
    }
}
