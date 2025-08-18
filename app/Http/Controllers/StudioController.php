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
        $user = Auth::user()->load('sources');

        return Inertia::render('Studio/Index', [
            'sources' => $user->sources
        ]);
    }

    public function patch(Request $request)
    {
        $user_id = Auth::id();

        $validated = $request->validate([
            'source_id' => [
                'required',
                Rule::exists('user_source', 'source_id')
                    ->where(fn($query) => $query->where('user_id', $user_id)),
            ],
            'model' => ['string'], // eventueel aanpassen
        ]);

        Source::findOrFail($validated['source_id'])
            ->update(['model' => $validated['model']]);

        return back();
    }
}
