<?php

namespace App\OpenApi\Notifications;

final class NotificationResponseExamples
{
    public const INDEX = [
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
    ];
}
