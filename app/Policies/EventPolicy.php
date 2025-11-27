<?php

class EventPolicy
{
    public function participate(User $user, Event $event)
    {
        return $user->isApproved() &&
            !$event->isFull() &&
            !$user->events()->where('event_id', $event->id)->exists();
    }
}
