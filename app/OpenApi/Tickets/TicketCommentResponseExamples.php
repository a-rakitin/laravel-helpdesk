<?php

namespace App\OpenApi\Tickets;

final class TicketCommentResponseExamples
{
    public const INDEX = [
        'data' => [[
            'id' => 87,
            'ticket_id' => 42,
            'user_id' => 3,
            'body' => 'We have reset your access. Please try again.',
            'created_at' => '2026-08-29T10:15:30.000000Z',
            'updated_at' => '2026-08-29T10:15:30.000000Z',
            'author' => [
                'id' => 3,
                'name' => 'Support Agent',
                'email' => 'agent@example.com',
                'role' => 'agent',
                'created_at' => null,
                'updated_at' => null,
            ],
        ]],
    ];

    public const STORE = [
        'data' => [
            'id' => 88,
            'ticket_id' => 42,
            'user_id' => 1,
            'body' => 'I can reproduce this issue.',
            'created_at' => '2026-08-29T11:00:00.000000Z',
            'updated_at' => '2026-08-29T11:00:00.000000Z',
        ],
    ];
}
