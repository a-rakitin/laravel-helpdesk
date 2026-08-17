<?php

namespace App\Http\Controllers;

use App\Http\Resources\NotificationResource;
use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\PathParameter;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    #[Endpoint(title: 'List notifications', description: 'Returns the authenticated user\'s notifications, ordered from newest to oldest.')]
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
