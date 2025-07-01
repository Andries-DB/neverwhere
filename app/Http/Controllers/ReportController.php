<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Report;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function get()
    {
        $user = Auth::user();
        $companyIds = $user->companies->pluck('id');

        // Rapporten gelinkt aan de user
        $userReports = Report::whereHas('user', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->get()->map(function ($report) {
            $report->type = 'user';
            return $report;
        });

        // Rapporten gelinkt aan de bedrijven van de user
        $companyReports = Report::whereHas('company', function ($query) use ($companyIds) {
            $query->whereIn('company_id', $companyIds);
        })->get()->map(function ($report) {
            $report->type = 'company';
            return $report;
        });

        // Unieke rapporten combineren
        $reports = $userReports->merge($companyReports)->unique('id')->values();

        return Inertia::render('Reports/Index', [
            'reports' => $reports
        ]);
    }
    public function store_company(Request $request)
    {
        $this->authorizeAdmin();

        $request->validate([
            'name' => 'required|string|max:255',
            'link' => 'nullable'
        ]);

        $report = Report::create([
            'guid' => (string) Str::uuid(),
            'name' => $request->name,
            'link' => $request->link,
        ]);

        $report->company()->attach($request->company);
    }

    public function store_user(Request $request)
    {
        $this->authorizeAdmin();

        $request->validate([
            'name' => 'required|string|max:255',
            'link' => 'nullable'
        ]);

        $report = Report::create([
            'guid' => (string) Str::uuid(),
            'name' => $request->name,
            'link' => $request->link,
        ]);

        $report->user()->attach($request->user);
    }

    public function read_company($guid, $report_guid)
    {
        $this->authorizeAdmin();

        $company = Company::where('guid', $guid)->firstOrFail();
        $report = Report::where('guid', $report_guid)->firstOrFail();

        abort_unless($report, 403, "This report does not exist");
        return Inertia::render('Company/Reports/Read', [
            'report' => $report,
            'company' => $company
        ]);
    }

    public function read_user($guid, $user_guid, $report_guid)
    {
        $this->authorizeAdmin();

        $user = User::where('guid', $user_guid)->firstOrFail();
        $company = Company::where('guid', $guid)->firstOrFail();
        $report = Report::where('guid', $report_guid)->firstOrFail();

        abort_unless($report, 403, "This report does not exist");
        return Inertia::render('Company/Reports/Read', [
            'report' => $report,
            'company' => $company,
        ]);
    }

    public function update_company($guid, $report_guid, Request $request)
    {
        $this->authorizeAdmin();

        $report = Report::where('guid', $report_guid)->first();
        $data = $request->all();
        $report->update($data);

        return redirect()->back();
    }

    public function update_user($guid, $report_guid, Request $request)
    {
        $this->authorizeAdmin();

        $report = Report::where('guid', $report_guid)->first();
        $data = $request->all();
        $report->update($data);

        return redirect()->back();
    }

    public function delete_company($guid, $report_guid)
    {

        $this->authorizeAdmin();

        $report = Report::where('guid', $report_guid)->first();

        abort_unless($report, 403, "This report does not exist");

        $report->delete();

        return redirect()->route('company.read', $guid);
    }

    public function delete_user($guid, $report_guid)
    {

        $this->authorizeAdmin();

        $report = Report::where('guid', $report_guid)->first();

        abort_unless($report, 403, "This report does not exist");

        $report->delete();

        return redirect()->route('user.read', $guid);
    }

    private function authorizeAdmin(): void
    {
        abort_unless(auth()->user()->role === 'admin', 403, 'You do not have permission to view this page');
    }
}
