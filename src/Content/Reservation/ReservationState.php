<?php

namespace App\Content\Reservation;

enum ReservationState: string
{
    case PENDING = 'pending';
    case RESERVED = 'reserved';
}
