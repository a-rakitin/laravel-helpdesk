<?php

namespace App\Http\Requests;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChangeTicketStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ticket = $this->route('ticket');

        return $ticket instanceof Ticket
            && ($this->user()?->can('changeStatus', $ticket) ?? false);
    }

    public function rules(): array
    {
        return [
            /**
             * New ticket status.
             *
             * @example in_progress
             */
            'status' => ['required', Rule::enum(TicketStatus::class)],
        ];
    }
}
