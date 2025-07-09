<?php

namespace App\Http\Controllers;

use App\Models\Company;
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

    public function read($guid, $source_id)
    {
        $this->authorizeAdmin();

        $company = Company::where('guid', $guid)->firstOrFail();
        $source = Source::find($source_id);

        abort_unless($source, 403, "This source does not exist");

        return Inertia::render('Company/Sources/Read', [
            'source' => $source,
            'company' => $company
        ]);
    }

    public function store(Request $request)
    {
        // dd($request->all());
        $this->authorizeAdmin();

        $request->validate([
            'name' => 'required|string|max:255',
            'webhook' => 'nullable',
            'model' => 'nullable',
        ]);

        Source::create([
            'name' => $request->name,
            'hex_color' => $request->color,
            'webhook' => $request->webhook,
            'model' => $request->model,
            'company_id' => $request->company
        ]);

        return redirect()->back();
    }

    public function update($guid, $source_id, Request $request)
    {
        $this->authorizeAdmin();

        $source = Source::where('id', $source_id)->first();
        $data = $request->all();
        $source->update($data);

        return redirect()->back();
    }

    public function delete($guid, $source_id)
    {
        $this->authorizeAdmin();

        $source = Source::where('id', $source_id)->first();

        abort_unless($source, 403, "This source does not exist");

        $source->delete();

        return redirect()->route('company.read', $guid);
    }

    private function authorizeAdmin(): void
    {
        abort_unless(auth()->user()->role === 'admin', 403, 'You do not have permission to view this page');
    }

    public function getSourceInfo(Request $request, Source $source)
    {
        // Valideer de access key
        $request->validate([
            'access_key' => 'required|string'
        ]);

        // Controleer of de access key correct is
        if (!$this->validateAccessKey($request->access_key, $source)) {
            return response()->json([
                'error' => 'Invalid access key'
            ], 403);
        }
        return response()->json([
            'success' => true,
            'data' => [
                'name' => $source->name,
                'model' => $source->model,
                'company' => $source->company->company ?? null,
            ]
        ]);
    }

    private function validateAccessKey(string $providedKey, Source $source): bool
    {
        // Optie 1: Vaste string voor alle sources
        $validKey = config('app.source_access_key', 'your-secret-key-here');

        return hash_equals($validKey, $providedKey);
    }
}
