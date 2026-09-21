<?php

namespace App\Http\Requests\Auth;

use Dedoc\Scramble\Attributes\SchemaName;
use Illuminate\Foundation\Http\FormRequest;

#[SchemaName('LoginRequest')]
class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            /**
             * Registered user email address.
             *
             * @example john@example.com
             */
            'email' => ['required', 'string', 'email'],

            /**
             * User password.
             *
             * @format password
             *
             * @example password123
             */
            'password' => ['required', 'string'],
        ];
    }
}
