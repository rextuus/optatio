<?php

namespace App\Content\Desire;

enum DesireState: string
{
    case FREE = 'free';
    case RESERVED = 'reserved';
    case MULTIPLE_RESERVED = 'multiple_reserved';
    case RESOLVED = 'resolved';
    case MULTIPLE_RESOLVED = 'multiple_resolved';
    case MULTIPLE_RESERVED_OR_RESOLVED = 'multiple_reserved_or_resolved';
}
