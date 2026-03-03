<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Ticket;
use App\Enums\UserRole;

class TicketPolicy
{
    public function view(User $user, Ticket $ticket): bool
    {
        if ($user->isAdmin() || $user->isAgent()) {
            return true;
        }

        return $ticket->created_by === $user->id;
    }

    public function update(User $user, Ticket $ticket): bool
    {
        if ($user->isAdmin() || $user->isAgent()) {
            return true;
        }

        return false;
    }
}
