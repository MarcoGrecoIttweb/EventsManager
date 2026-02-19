<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Event;

class EventPolicy
{
    public function participate(User $user, Event $event)
    {
        return $user->isApproved() &&
            !$event->isFull() &&
            !$event->participants()->where('utente.userID', $user->getKey())->exists();
    }
}
