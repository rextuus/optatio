<?php
declare(strict_types=1);

namespace App\Content\Reservation;

use App\Content\Reservation\Data\ReservationData;
use App\Entity\Reservation;

/**
 * @author Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2023 DocCheck Community GmbH
 */
class ReservationFactory
{
    public function createByData(ReservationData $data): Reservation
    {
        $reservation = $this->createNewInstance();
        $this->mapData($data, $reservation);
        return $reservation;
    }
    
    public function mapData(ReservationData $data, Reservation $reservation): Reservation
    {
        $reservation->setDesire($data->getDesire());
        $reservation->setOwner($data->getOwner());
        $reservation->setState($data->getState());

        return $reservation;
    }
    
    private function createNewInstance(): Reservation
    {
        return new Reservation();
    }
}
