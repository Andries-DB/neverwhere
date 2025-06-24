<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MessageController extends Controller
{
    public function read($guid)
    {
        $message = Message::where('guid', $guid)->with('conversation', 'user')->firstOrFail();

        return Inertia::render('Message/Read', [
            'message' => $message,
        ]);
    }
}
