<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;

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

    public function viewAny(User $user): bool
    {
        return in_array($user->role->value, ['admin', 'agent', 'customer'], true);
    }

    public function assign(User $user, Ticket $ticket): bool
    {
        return $user->isAdmin() || $user->isAgent();
    }

    public function changeStatus(User $user, Ticket $ticket): bool
    {
        return $user->isAdmin() || $user->isAgent();
    }
}
