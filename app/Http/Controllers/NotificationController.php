<?php

namespace App\Http\Controllers;

use Dedoc\Scramble\Attributes\Endpoint;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    #[Endpoint(
        title: 'List notifications',
        description: 'Returns notifications for the authenticated user only.'
    )]
    public function index(Request $request)
    {
        return response()->json([
            'data' => $request->user()
                ->notifications()
                ->latest()
                ->get(),
        ]);
    }

    /**
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    #[Endpoint(
        title: 'Mark notification as read',
        description: 'Marks one notification as read when it belongs to the authenticated user. Unknown notification IDs and notifications owned by another user return 404.'
    )]
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
