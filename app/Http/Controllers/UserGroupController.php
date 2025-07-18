<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\UserGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class UserGroupController extends Controller
{
    public function store($guid, Request $request)
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'company' => 'required|exists:companies,id',
            'sources' => 'array',
            'sources.*' => 'exists:sources,id',
        ]);

        // UserGroup aanmaken
        $user_group = UserGroup::create([
            'guid' => Str::uuid(),
            'company_id' => $validated['company'],
            'name' => $validated['name'],
        ]);

        // Sources koppelen via pivot
        if (!empty($validated['sources'])) {
            $user_group->sources()->sync($validated['sources']);
        }

        return redirect()->back();
    }

    public function read($guid, $usergroup_guid)
    {
        $this->authorizeAdmin();

        $company = Company::where('guid', $guid)->with('sources')->firstOrFail();

        $user_group = UserGroup::where('guid', $usergroup_guid)
            ->where('company_id', $company->id)
            ->with('sources')
            ->firstOrFail();

        return Inertia::render('Company/UserGroups/Read', [
            'user_group' => $user_group,
            'company' => $company,
        ]);
    }

    public function update($guid, $usergroup_guid, Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'source_ids' => 'array',
            'source_ids.*' => 'integer|exists:sources,id',
        ]);

        $company = Company::where('guid', $guid)->firstOrFail();
        $user_group = $company->usergroups()->where('guid', $usergroup_guid)->firstOrFail();

        $user_group->update([
            'name' => $request->name,
        ]);

        $user_group->sources()->sync($request->source_ids ?? []);

        return redirect()->back()->with('success', 'Gebruiker succesvol bijgewerkt.');
    }

    public function delete($guid, $usergroup_guid)
    {
        $this->authorizeAdmin();

        $user_group = UserGroup::where('guid', $usergroup_guid)->first();

        abort_unless($user_group, 403, "This source does not exist");

        $user_group->delete();

        return redirect()->route('company.read', $guid);
    }

    private function authorizeAdmin(): void
    {
        abort_unless(auth()->user()->role === 'admin', 403, 'You do not have permission to view this page');
    }
}
