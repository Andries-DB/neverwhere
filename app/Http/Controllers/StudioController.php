<?php

namespace App\Http\Controllers;

use App\Models\Source;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class StudioController extends Controller
{
    public function get()
    {
        $user = Auth::user()->load('sources', 'sources.suggestions');

        return Inertia::render('Studio/Index', [
            'sources' => $user->sources
        ]);
    }

    public function patch(Request $request)
    {
        // dd($request->all());
        $user_id = Auth::id();

        $validated = $request->validate([
            'source_id' => [
                'required',
                Rule::exists('user_source', 'source_id')
                    ->where(fn($query) => $query->where('user_id', $user_id)),
            ],
            'model' => ['string'], // eventueel aanpassen
            'suggestions' => ['array'],
        ]);

        $source = Source::findOrFail($validated['source_id']);
        $source->update(['model' => $validated['model']]);

        $source->suggestions()->delete();
        $source->suggestions()->createMany(
            collect($validated['suggestions'])
                ->filter(fn($s) => !empty($s['question'])) // alleen met question ingevuld
                ->map(fn($s) => [
                    'question' => $s['question'],
                    'user_id' => Auth::id(),
                ])
                ->toArray()
        );

        return back();
    }
}
