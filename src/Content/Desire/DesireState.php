<?php

namespace App\Content\Desire;

enum DesireState: string
{
    case FREE = 'free';
    case RESERVED = 'reserved';
    case MULTIPLE_RESERVED = 'multiple_reserved';
    case RESOLVED = 'resolved';
    case MULTIPLE_RESOLVED = 'multiple_resolved';
}
