<?php

namespace App\Http\Requests\Auth;

use Dedoc\Scramble\Attributes\BodyParameter;
use Dedoc\Scramble\Attributes\SchemaName;
use Illuminate\Foundation\Http\FormRequest;

#[SchemaName('RegisterRequest')]
#[BodyParameter('password_confirmation', description: 'Must match the password field.', format: 'password', example: 'password123')]
class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            /**
             * Customer display name.
             *
             * @example John Doe
             */
            'name' => ['required', 'string', 'max:255'],

            /**
             * Unique customer email address.
             *
             * @example john@example.com
             */
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],

            /**
             * User password.
             *
             * @format password
             *
             * @example password123
             */
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }
}
