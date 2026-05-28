<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChangeUserRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->route('user');

        return $user instanceof User
            && ($this->user()?->can('changeRole', $user) ?? false);
    }

    public function rules(): array
    {
        return [
            /**
             * New role for the user.
             *
             * Supported values: admin, agent, customer.
             *
             * @example agent
             */
            'role' => ['required', Rule::enum(UserRole::class)],
        ];
    }
}
