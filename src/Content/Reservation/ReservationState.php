<?php

namespace App\Content\Reservation;

enum ReservationState: string
{
    case RESOLVED = 'resolved';
    case RESERVED = 'reserved';
}
