<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Request as RequestModel;

class RequestController extends Controller
{
    public function get()
    {
        return Inertia::render('Report');
    }

    public function get_admin()
    {
        $requests = RequestModel::with('user')->get();

        return Inertia::render('ReportOverview', [
            'requests' => $requests,
        ]);
    }

    public function post(Request $request)
    {

        $user = auth()->user();
        $request->validate([
            'type' => 'required|string|max:255',
            'title' => 'required|string|max:255',
        ]);

        RequestModel::create([
            'user_id' => $user->id,
            'type' => $request->type,
            'title' => $request->title,
            'steps' => $request->steps,
            'description' => $request->description,
            'priority' => $request->priority,
            'environment' => $request->environment,
        ]);

        return redirect()->back();
    }

    public function delete($id)
    {
        $request = RequestModel::find($id);

        abort_unless($request, 403, "This request does not exist");

        $request->delete();

        return redirect()->route('reports.get.admin');
    }
}
