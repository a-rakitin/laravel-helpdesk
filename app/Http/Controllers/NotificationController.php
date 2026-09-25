<?php

namespace App\Http\Controllers;

use App\Http\Resources\NotificationResource;
use App\OpenApi\Notifications\NotificationResponseExamples;
use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\PathParameter;
use Dedoc\Scramble\Attributes\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    #[Endpoint(title: 'List notifications', description: 'Returns the authenticated user\'s notifications, ordered from newest to oldest.')]
    #[Response(status: 200, description: 'Authenticated user notifications', examples: [NotificationResponseExamples::INDEX])]
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
    #[Response(status: 200, description: 'Notification marked as read')]
    #[PathParameter('id', description: 'Notification UUID.', format: 'uuid', example: '018f2b2b-9b67-7d6d-a2e3-1d4b5c6d7e8f')]
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
