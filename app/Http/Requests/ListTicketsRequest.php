<?php

namespace App\Http\Requests;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListTicketsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (! $this->has('mine') || ! is_string($this->input('mine'))) {
            return;
        }

        $this->merge([
            'mine' => match (strtolower($this->input('mine'))) {
                'true' => true,
                'false' => false,
                default => $this->input('mine'),
            },
        ]);
    }

    public function rules(): array
    {
        return [
            /**
             * Filter by ticket status.
             *
             * Typical values: open, in_progress, closed.
             *
             * @example open
             */
            'status' => ['nullable', Rule::enum(TicketStatus::class)],

            /**
             * Filter by ticket priority.
             *
             * Typical values: low, medium, high.
             *
             * @example high
             */
            'priority' => ['nullable', Rule::enum(TicketPriority::class)],

            /**
             * Filter by assigned user ID. The ID must exist in users.
             *
             * @example 2
             */
            'assigned_to' => ['nullable', 'integer', Rule::exists('users', 'id')],

            /**
             * For agents and admins: when true, return only tickets assigned to the current user.
             *
             * @example true
             */
            'mine' => ['nullable', 'boolean'],

            /**
             * Search by ticket title or description.
             *
             * @example login issue
             */
            'search' => ['nullable', 'string', 'max:255'],

            /**
             * Sort field.
             *
             * Supported values in this endpoint: created_at, priority, status.
             *
             * @example priority
             */
            'sort' => ['nullable', Rule::in(['created_at', 'priority', 'status'])],

            /**
             * Sort direction.
             *
             * Supported values in this endpoint: asc, desc.
             *
             * @example asc
             */
            'direction' => ['nullable', Rule::in(['asc', 'desc'])],

            /**
             * Number of tickets per page.
             *
             * @example 50
             */
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
