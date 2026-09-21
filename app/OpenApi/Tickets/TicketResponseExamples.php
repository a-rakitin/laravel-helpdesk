<?php

namespace App\OpenApi\Tickets;

final class TicketResponseExamples
{
    public const INDEX = [
        'data' => [[
            'id' => 42,
            'title' => 'Cannot access account',
            'description' => 'The user cannot sign in after resetting the password.',
            'status' => 'open',
            'priority' => 'high',
            'created_by' => 1,
            'assigned_to' => 3,
            'created_at' => '2026-08-27T09:15:30.000000Z',
            'updated_at' => '2026-08-27T09:15:30.000000Z',
        ]],
        'meta' => [
            'current_page' => 1,
            'per_page' => 15,
            'total' => 1,
            'last_page' => 1,
        ],
    ];

    public const STORE = [
        'data' => [
            'id' => 42,
            'title' => 'Cannot sign in',
            'description' => 'Login fails after password reset.',
            'status' => 'open',
            'priority' => 'high',
            'created_by' => 1,
            'assigned_to' => null,
            'created_at' => '2026-08-28T09:15:30.000000Z',
            'updated_at' => '2026-08-28T09:15:30.000000Z',
        ],
    ];

    public const SHOW = [
        'data' => [
            'id' => 42,
            'title' => 'Cannot access account',
            'description' => 'The user cannot sign in after resetting the password.',
            'status' => 'open',
            'priority' => 'high',
            'created_by' => 1,
            'assigned_to' => 3,
            'created_at' => '2026-08-29T09:15:30.000000Z',
            'updated_at' => '2026-08-29T09:15:30.000000Z',
        ],
    ];

    public const ASSIGN = [
        'data' => [
            'id' => 42,
            'title' => 'Cannot access account',
            'description' => 'The user cannot sign in after resetting the password.',
            'status' => 'open',
            'priority' => 'high',
            'created_by' => 1,
            'assigned_to' => 2,
            'created_at' => '2026-08-28T09:15:30.000000Z',
            'updated_at' => '2026-08-29T10:20:00.000000Z',
        ],
    ];

    public const CHANGE_STATUS = [
        'data' => [
            'id' => 42,
            'title' => 'Cannot access account',
            'description' => 'The user cannot sign in after resetting the password.',
            'status' => 'in_progress',
            'priority' => 'high',
            'created_by' => 1,
            'assigned_to' => 2,
            'created_at' => '2026-08-28T09:15:30.000000Z',
            'updated_at' => '2026-08-29T10:30:00.000000Z',
        ],
    ];
}
