<?php

namespace App\Http\Requests;

use App\Enums\TicketPriority;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            /**
             * Ticket title.
             *
             * @example Cannot sign in
             */
            'title' => ['required', 'string', 'max:255'],

            /**
             * Ticket description.
             *
             * @example Login fails after password reset.
             */
            'description' => ['required', 'string'],

            /**
             * Ticket priority.
             *
             * @default medium
             *
             * @example high
             */
            'priority' => ['nullable', Rule::enum(TicketPriority::class)],
        ];
    }
}
