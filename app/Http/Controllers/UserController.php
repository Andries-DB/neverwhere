<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Source;
use App\Models\User;
use App\Notifications\UserInvitationNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index()
    {
        $this->authorizeAdmin();
        $users = User::with('companies')->get();

        return Inertia::render('Users/Index', [
            'users' => $users,
        ]);
    }
    public function store($guid, Request $request)
    {
        $this->authorizeAdmin();

        // dd($request->all());
        $request->validate([
            'name' => 'required|string|max:255',
            'firstname'  => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => ['required', PasswordRule::defaults()],
        ]);

        $company = Company::where('guid', $guid)->with('users')->firstOrFail();

        $user = User::create([
            'name' => $request->name,
            'firstname' => $request->firstname,
            'guid' => (string) Str::uuid(),
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'user',
        ]);

        $token = Password::createToken($user);
        $user->notify(new UserInvitationNotification($token));

        $company->users()->attach($user->id);

        foreach ($request->sources as $source) {
            $user->sources()->attach($source);
        }

        return redirect()->back();
    }

    public function show($guid, $user_guid)
    {
        $this->authorizeAdmin();
        $company = Company::where('guid', $guid)->with('sources')->firstOrFail();
        $user = User::where('guid', $user_guid)->with('sources', 'reports')->firstOrFail();


        return Inertia::render('Company/Users/Read', [
            'company' => $company,
            'user' => $user,
        ]);
    }

    public function read($guid)
    {
        $this->authorizeAdmin();

        $user = User::where('guid', $guid)->with('sources')->firstOrFail();

        $sources = Source::all();

        return Inertia::render('Users/Read', [
            'user' => $user,
            'sources' => $sources
        ]);
    }

    public function update($guid, $user_guid, Request $request)
    {
        $this->authorizeAdmin();

        $request->validate([
            'name' => 'required|string|max:255',
            'firstname'  => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore(User::where('guid', $user_guid)->first()?->id),
            ],
            'source_ids' => 'array',
            'source_ids.*' => 'integer|exists:sources,id',
        ]);

        $company = Company::where('guid', $guid)->firstOrFail();
        $user = $company->users()->where('guid', $user_guid)->firstOrFail();

        $user->update([
            'name' => $request->name,
            'firstname' => $request->firstname,
            'email' => $request->email,
        ]);

        // Synchroniseer de gekoppelde bronnen
        $user->sources()->sync($request->source_ids ?? []);

        return redirect()->back()->with('success', 'Gebruiker succesvol bijgewerkt.');
    }

    public function update_only_user($guid, Request $request)
    {
        $this->authorizeAdmin();

        $request->validate([
            'firstname' => $request->firstname,
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore(User::where('guid', $guid)->first()?->id),
            ],
        ]);

        $user = User::where('guid', $guid)->firstOrFail();

        $user->update([
            'name' => $request->name,
            'firstname' => $request->firstname,
            'email' => $request->email,
        ]);

        return redirect()->back()->with('success', 'Gebruiker succesvol bijgewerkt.');
    }

    public function delete($guid, $user_guid)
    {
        $this->authorizeAdmin();
        $user = User::where('guid', $user_guid)->first();

        abort_unless($user, 403, "This source does not exist");

        $user->delete();

        return redirect()->route('company.read', $guid);
    }

    public function delete_only_user($guid)
    {
        $this->authorizeAdmin();
        $user = User::where('guid', $guid)->first();

        abort_unless($user, 403, "This source does not exist");

        $user->delete();

        return redirect()->route('user.get');
    }

    private function authorizeAdmin(): void
    {
        abort_unless(auth()->user()->role === 'admin', 403, 'You do not have permission to view this page');
    }
}
