<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketComment;
use App\Notifications\TicketCommentAddedNotification;
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

        $recipients = collect([
            $ticket->creator,
            $ticket->assignee,
        ])
            ->filter()
            ->unique('id')
            ->reject(fn ($user) => $user->id === $request->user()->id);

        foreach ($recipients as $recipient) {
            $recipient->notify(new TicketCommentAddedNotification($ticket, $comment));
        }

        return response()->json($comment, 201);
    }
}
