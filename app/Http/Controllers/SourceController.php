<?php

namespace App\Http\Controllers;

use App\Models\Source;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SourceController extends Controller
{
    public function index()
    {
        $this->authorizeAdmin();
        $sources = Source::all();
        return Inertia::render('Sources/Index', [
            'sources' => $sources,
        ]);
    }

    public function read($id)
    {
        $this->authorizeAdmin();

        $source = Source::find($id);

        abort_unless($source, 403, "This source does not exist");

        return Inertia::render('Sources/Read', [
            'source' => $source
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizeAdmin();

        $request->validate([
            'name' => 'required|string|max:255|unique:sources,name',
            'color' => 'required',
            'webhook' => 'nullable'
        ]);

        Source::create([
            'name' => $request->name,
            'hex_color' => $request->color,
            'webhook' => $request->webhook
        ]);

        return redirect()->route('source.get')->with('success', 'Source aangemaakt.');
    }

    public function update($id, Request $request)
    {
        $this->authorizeAdmin();

        $source = Source::where('id', $id)->first();
        $data = $request->all();
        $source->update($data);

        return redirect()->back();
    }

    public function delete($id)
    {
        $this->authorizeAdmin();

        $source = Source::where('id', $id)->first();

        abort_unless($source, 403, "This source does not exist");

        $source->delete();

        return redirect()->route('source.get');
    }

    private function authorizeAdmin(): void
    {
        abort_unless(auth()->user()->role === 'admin', 403, 'You do not have permission to view this page');
    }
}
