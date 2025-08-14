<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Dashboard;
use App\Models\PinnedGraph;
use App\Models\PinnedItem;
use App\Models\PinnedTable;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
use LDAP\Result;

class DashboardController extends Controller
{
    public function show($guid = null)
    {
        $user = auth()->user();
        $user->load('dashboards');

        $dashboard = Dashboard::query()
            ->when($guid, fn($q) => $q->where('guid', $guid))
            ->when(!$guid, fn($q) => $q->where('default', 1))
            ->where('user_id', $user->id)
            ->with([

                'pinned_items.message.conversation',
            ])
            ->first();

        // 2. Indien niet gevonden, maak een nieuwe aan
        if (!$dashboard) {
            $dashboard = Dashboard::create([
                'user_id' => $user->id,
                'name' => 'Pinboard ' . $user->firstname,
                'default' => 1,
                'guid' => Str::uuid(),
            ]);

            // Leeg laden zodat je geen fouten krijgt in frontend
            $dashboard->loadMissing([

                'pinned_items.message.conversation',

            ]);
        }

        return Inertia::render('Dashboard', [
            'dashboard' => $dashboard,

            'pinned_items' => $dashboard->pinned_items,
            'all_dashboards' => $user->dashboards
        ]);
    }

    public function create(Request $request)
    {

        $request->validate([
            'name' => 'required|max:255'
        ]);

        $user = auth()->user();

        Dashboard::create([
            'user_id' => $user->id,
            'name' => $request->name,
            'default' => 0,
            'guid' => Str::uuid(),
        ]);

        return redirect()->back();
    }

    public function updateItemOrder(Request $request)
    {
        foreach ($request->graphs as $graph) {
            $pinned_item = PinnedItem::find($graph['id']);
            $pinned_item->display_order = $graph['display_order'];
            $pinned_item->save();
        }

        return [
            'csrf_token' => csrf_token(),
        ];
    }

    public function makeDefault(Request $request)
    {
        $user = auth()->user();

        $dashboard = Dashboard::where('user_id', $user->id)
            ->where('default', 1)
            ->first();

        $dashboard->default = 0;
        $dashboard->save();

        $dashboard_to_make_default = Dashboard::find($request->dashboard['id']);
        $dashboard_to_make_default->default = 1;
        $dashboard_to_make_default->save();

        return redirect()->route('dashboard', $dashboard_to_make_default->guid);
    }

    public function delete(Request $request)
    {
        $user = auth()->user();

        $dashboard = Dashboard::where('user_id', $user->id)
            ->where('id', $request->dashboard['id'])
            ->firstOrFail();

        $isDefault = $dashboard->default;

        // Verwijder de dashboard
        $dashboard->delete();

        // Als het de default was, maak de eerste overblijvende dashboard default
        if ($isDefault) {
            $newDefault = Dashboard::where('user_id', $user->id)->first();

            if ($newDefault) {
                $newDefault->default = 1;
                $newDefault->save();

                return redirect()->route('dashboard', $newDefault->guid);
            } else {
                return redirect()->route('dashboard');
            }
        }

        // Als het geen default was → redirect naar huidige default dashboard
        $default = Dashboard::where('user_id', $user->id)
            ->where('default', 1)
            ->first();

        return redirect()->route('dashboard', $default?->guid ?? '');
    }
}
