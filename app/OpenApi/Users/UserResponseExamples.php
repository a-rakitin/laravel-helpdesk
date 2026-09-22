<?php

namespace App\OpenApi\Users;

final class UserResponseExamples
{
    public const INDEX = [
        'data' => [[
            'id' => 3,
            'name' => 'Support Agent',
            'email' => 'agent@example.com',
            'role' => 'agent',
            'created_at' => '2026-08-19T10:25:30.000000Z',
            'updated_at' => '2026-08-29T10:20:00.000000Z',
        ]],
        'meta' => [
            'current_page' => 1,
            'per_page' => 15,
            'total' => 1,
            'last_page' => 1,
        ],
    ];

    public const CHANGE_ROLE = [
        'data' => [
            'id' => 3,
            'name' => 'Support Agent',
            'email' => 'agent@example.com',
            'role' => 'agent',
            'created_at' => '2026-08-19T10:25:30.000000Z',
            'updated_at' => '2026-08-30T10:30:00.000000Z',
        ],
    ];
}
