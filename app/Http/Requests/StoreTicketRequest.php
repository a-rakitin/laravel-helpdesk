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
             * Detailed problem description.
             *
             * @example Login fails after password reset.
             */
            'description' => ['required', 'string'],

            /**
             * Ticket priority. Defaults to medium when omitted.
             *
             * @example high
             */
            'priority' => ['nullable', Rule::enum(TicketPriority::class)],
        ];
    }
}
