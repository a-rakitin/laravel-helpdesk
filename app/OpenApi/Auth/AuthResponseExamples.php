<?php

namespace App\OpenApi\Auth;

final class AuthResponseExamples
{
    public const USER = [
        'id' => 1,
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'role' => 'customer',
        'created_at' => '2026-08-30T11:00:00.000000Z',
        'updated_at' => '2026-08-30T11:00:00.000000Z',
    ];

    public const TOKEN = '1|aB3dEfGhIjKlMnOpQrStUvWxYz0123456789AbCd';

    public const AUTHENTICATED_USER = [
        'user' => self::USER,
        'token' => self::TOKEN,
    ];

    public const CURRENT_USER = [
        'user' => self::USER,
    ];
}
