<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketComment;
use Illuminate\Http\Request;

class TicketCommentController extends Controller
{
    public function index(Request $request, Ticket $ticket)
    {
        $this->authorize('view', $ticket);

        $comments = $ticket->comments()
            ->with('author:id,name,email,role')
            ->latest()
            ->get();

        return response()->json($comments);
    }

    public function store(Request $request, Ticket $ticket)
    {
        $this->authorize('view', $ticket);

        $data = $request->validate([
            'body' => ['required', 'string'],
        ]);

        $comment = TicketComment::create([
            'ticket_id' => $ticket->id,
            'user_id' => $request->user()->id,
            'body' => $data['body'],
        ]);

        return response()->json($comment, 201);
    }
}
