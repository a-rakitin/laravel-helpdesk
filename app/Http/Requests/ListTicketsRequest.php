<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ListTicketsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
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
            'status' => ['nullable', 'string'],

            /**
             * Filter by ticket priority.
             *
             * Typical values: low, medium, high.
             *
             * @example high
             */
            'priority' => ['nullable', 'string'],

            /**
             * Filter by assigned agent user ID.
             *
             * @example 2
             */
            'assigned_to' => ['nullable', 'integer'],

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
             * Unknown values are ignored and fall back to created_at.
             *
             * @example priority
             */
            'sort' => ['nullable', 'string'],

            /**
             * Sort direction.
             *
             * Supported values in this endpoint: asc, desc.
             * Unknown values are ignored and fall back to desc.
             *
             * @example asc
             */
            'direction' => ['nullable', 'string'],

            /**
             * Number of tickets per page.
             *
             * The controller clamps this value to the 1..100 range.
             *
             * @example 50
             */
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
