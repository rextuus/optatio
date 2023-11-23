<?php
declare(strict_types=1);

namespace App\Content\Reservation;

use App\Content\Reservation\Data\ReservationData;
use App\Entity\Reservation;

/**
 * @author Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2023 DocCheck Community GmbH
 */
class ReservationService
{
    public function __construct(private readonly ReservationRepository $repository, private readonly ReservationFactory $factory)
    {
    }

    public function createByData(ReservationData $data): Reservation
    {
        $reservation = $this->factory->createByData($data);
        $this->repository->save($reservation);
        return $reservation;
    }

    public function update(Reservation $reservation, ReservationData $data): Reservation
    {
        $reservation = $this->factory->mapData($data, $reservation);
        $this->repository->save($reservation);
        return $reservation;
    }

    /**
     * @return Reservation[]
     */
    public function findBy(array $conditions): array
    {
        return $this->repository->findBy($conditions);
    }

    public function deleteReservation(Reservation $reservation)
    {
        $this->repository->delete($reservation);
    }
}
