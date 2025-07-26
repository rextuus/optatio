<?php
declare(strict_types=1);

namespace App\Content\Reservation\Data;

use App\Content\Reservation\ReservationState;
use App\Entity\Desire;
use App\Entity\Reservation;
use App\Entity\User;


class ReservationData
{
    private Desire $desire;
    private User $owner;
    private ReservationState $state;

    public function getDesire(): Desire
    {
        return $this->desire;
    }

    public function setDesire(Desire $desire): ReservationData
    {
        $this->desire = $desire;
        return $this;
    }

    public function getOwner(): User
    {
        return $this->owner;
    }

    public function setOwner(User $owner): ReservationData
    {
        $this->owner = $owner;
        return $this;
    }

    public function getState(): ReservationState
    {
        return $this->state;
    }

    public function setState(ReservationState $state): ReservationData
    {
        $this->state = $state;
        return $this;
    }

    public function initFromEntity(Reservation $reservation): ReservationData
    {
        $this->setOwner($reservation->getOwner());
        $this->setState($reservation->getState());
        $this->setDesire($reservation->getDesire());

        return $this;
    }
}
