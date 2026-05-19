<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTicketCommentRequest;
use App\Http\Resources\TicketCommentResource;
use App\Models\Ticket;
use App\Models\TicketComment;
use App\Notifications\TicketCommentAddedNotification;
use Dedoc\Scramble\Attributes\Endpoint;
use Illuminate\Http\Request;

class TicketCommentController extends Controller
{
    #[Endpoint(
        title: 'List ticket comments',
        description: 'Returns comments for a ticket visible to the authenticated user as { data: TicketCommentResource[] }. Customers can list comments only for their own tickets; agents and admins can list comments for any ticket.'
    )]
    public function index(Request $request, Ticket $ticket)
    {
        $this->authorize('view', $ticket);

        $comments = $ticket->comments()
            ->with('author:id,name,email,role')
            ->latest()
            ->get();

        return TicketCommentResource::collection($comments);
    }

    #[Endpoint(
        title: 'Create ticket comment',
        description: 'Adds a comment to a visible ticket and returns { data: TicketCommentResource }. Customers can comment only on their own tickets; agents and admins can comment on any ticket. The ticket creator and assignee are notified except for the comment author.'
    )]
    public function store(StoreTicketCommentRequest $request, Ticket $ticket)
    {
        $data = $request->validated();

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

        return TicketCommentResource::make($comment)
            ->response()
            ->setStatusCode(201);
    }
}
