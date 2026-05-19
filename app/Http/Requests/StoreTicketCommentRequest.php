<?php

namespace App\Http\Requests;

use App\Models\Ticket;
use Illuminate\Foundation\Http\FormRequest;

class StoreTicketCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ticket = $this->route('ticket');

        return $ticket instanceof Ticket
            && ($this->user()?->can('view', $ticket) ?? false);
    }

    public function rules(): array
    {
        return [
            /**
             * Comment body.
             *
             * @example I can reproduce this issue.
             */
            'body' => ['required', 'string'],
        ];
    }
}
