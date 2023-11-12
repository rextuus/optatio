<?php

namespace App\Content\Event;

enum EventType: string
{
    case NONE = 'none';
    case SECRET_SANTA = 'secret_santa';
    case BIRTHDAY = 'birthday';

    public function shouldBeOpen(): bool
    {
        return match($this) {
            EventType::SECRET_SANTA, EventType::BIRTHDAY, EventType::NONE => true,
        };
    }
}
