<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class ListUsersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', User::class) ?? false;
    }

    public function rules(): array
    {
        return [
            /**
             * Number of users per page.
             *
             * @example 50
             */
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
