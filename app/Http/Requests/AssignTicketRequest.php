<?php

namespace App\Http\Requests;

use App\Models\Ticket;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ticket = $this->route('ticket');

        return $ticket instanceof Ticket
            && ($this->user()?->can('assign', $ticket) ?? false);
    }

    public function rules(): array
    {
        return [
            /**
             * Existing agent user ID.
             *
             * @example 2
             */
            'assigned_to' => ['required', 'integer', Rule::exists('users', 'id')],
        ];
    }
}
