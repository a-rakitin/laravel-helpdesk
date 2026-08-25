<?php

namespace App\Http\Controllers;

use App\Http\Resources\NotificationResource;
use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\PathParameter;
use Dedoc\Scramble\Attributes\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    #[Endpoint(
        title: 'List notifications',
        description: 'Returns the authenticated user\'s notifications, ordered from newest to oldest.'
    )]
    #[Response(
        status: 200,
        description: 'Authenticated user notifications.',
        examples: [[
            'data' => [[
                'id' => '018f2b2b-9b67-7d6d-a2e3-1d4b5c6d7e8f',
                'type' => 'App\\Notifications\\TicketCommentAddedNotification',
                'data' => [
                    'ticket_id' => 42,
                    'ticket_title' => 'Cannot access account',
                    'comment_id' => 87,
                    'comment_body' => 'We have reset your access. Please try again.',
                    'comment_author_id' => 3,
                ],
                'read_at' => null,
                'created_at' => '2026-08-24T10:15:30.000000Z',
            ]],
        ]],
    )]
    public function index(Request $request)
    {
        return NotificationResource::collection(
            $request->user()
                ->notifications()
                ->latest()
                ->get()
        );
    }

    /**
     * @throws ModelNotFoundException
     */
    #[Endpoint(title: 'Mark notification as read', description: 'Marks one of the authenticated user\'s notifications as read.')]
    #[PathParameter('id', description: 'Notification UUID.', format: 'uuid')]
    public function markAsRead(Request $request, string $id)
    {
        $notification = $request->user()
            ->notifications()
            ->where('id', $id)
            ->firstOrFail();

        $notification->markAsRead();

        return response()->json([
            'message' => 'Notification marked as read.',
        ]);
    }
}
