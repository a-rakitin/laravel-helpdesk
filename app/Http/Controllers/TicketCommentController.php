<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketComment;
use App\Notifications\TicketCommentAddedNotification;
use Dedoc\Scramble\Attributes\Endpoint;
use Illuminate\Http\Request;

class TicketCommentController extends Controller
{
    #[Endpoint(
        title: 'List ticket comments',
        description: 'Returns comments for a ticket visible to the authenticated user. Customers can list comments only for their own tickets; agents and admins can list comments for any ticket.'
    )]
    public function index(Request $request, Ticket $ticket)
    {
        $this->authorize('view', $ticket);

        $comments = $ticket->comments()
            ->with('author:id,name,email,role')
            ->latest()
            ->get();

        return response()->json($comments);
    }

    #[Endpoint(
        title: 'Create ticket comment',
        description: 'Adds a comment to a visible ticket. Customers can comment only on their own tickets; agents and admins can comment on any ticket. The ticket creator and assignee are notified except for the comment author.'
    )]
    public function store(Request $request, Ticket $ticket)
    {
        $this->authorize('view', $ticket);

        $data = $request->validate([
            /**
             * Comment body.
             *
             * @example I can reproduce this issue.
             */
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
