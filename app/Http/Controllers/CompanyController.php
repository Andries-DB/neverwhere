<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Source;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;

class CompanyController extends Controller
{
    public function index()
    {
        $this->authorizeAdmin();
        $companies = Company::all();

        return Inertia::render('Company/Index', [
            'companies' => $companies,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'company' => 'required|string|max:255',
        ]);

        Company::create([
            'guid' => (string) Str::uuid(),
            'company' => $validated['company'],
        ]);

        return redirect()->route('company.get')->with('success', 'Company aangemaakt.');
    }

    public function show($guid)
    {

        $this->authorizeAdmin();

        $company = Company::where('guid', $guid)->with('users', 'users.sources', 'sources')->firstOrFail();
        // $sources = Source::all();

        return Inertia::render('Company/Read', [
            'company' => $company
            // 'sources' => $sources
        ]);
    }

    public function update($guid, Request $request)
    {
        $this->authorizeAdmin();

        $company = Company::where('guid', $guid)->first();
        $data = $request->all();

        $company->update($data);

        return redirect()->back();
    }

    public function delete($guid, Request $request)
    {
        $this->authorizeAdmin();

        $company = Company::where('guid', $guid)->first();

        abort_unless($company, 403, "This company does not exist");

        $company->delete();

        return redirect()->route('company.get');
    }


    private function authorizeAdmin(): void
    {
        abort_unless(auth()->user()->role === 'admin', 403, 'You do not have permission to view this page');
    }
}
