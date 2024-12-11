<?php

declare(strict_types=1);

namespace App\Content\Desire;

use App\Content\Desire\Data\DesireData;
use App\Content\DesireList\Data\DesireListData;
use App\Content\DesireList\DesireListService;
use App\Content\Image\ImageService;
use App\Content\Priority\Data\PriorityData;
use App\Content\Priority\PriorityService;
use App\Content\Reservation\Data\ReservationData;
use App\Content\Reservation\ReservationService;
use App\Content\Reservation\ReservationState;
use App\Content\User\AccessRoleService;
use App\Entity\Desire;
use App\Entity\DesireList;
use App\Entity\Event;
use App\Entity\Image;
use App\Entity\Reservation;
use App\Entity\SecretSantaEvent;
use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @author Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2023 DocCheck Community GmbH
 */
class DesireManager
{
    private const MASTER_LIST_ROLE = 'ROLE_MASTER_LIST';

    public function __construct(
        private readonly DesireService $desireService,
        private readonly DesireListService $desireListService,
        private readonly PriorityService $priorityService,
        private readonly AccessRoleService $accessRoleService,
        private readonly ReservationService $reservationService,
        private readonly ImageService $imageService,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function initPriorityForDesire(Desire $desire): void
    {
        foreach ($desire->getDesireLists() as $desireList) {
            $highestPriority = $this->priorityService->getHighestPriorityByList($desireList);
            $data = new PriorityData();
            $data->setDesire($desire);
            $data->setDesireList($desireList);
            $data->setValue($highestPriority + 1);

            $this->priorityService->createByData($data);
        }
    }

    public function increasePriority(DesireList $desireList, Desire $desire): void
    {
        $priorities = $this->priorityService->findBy(['desireList' => $desireList, 'desire' => $desire]);
        if (count($priorities) !== 1) {
            throw new Exception('No unique Priority found for desire/desireList combination');
        }
        $priority = $priorities[0];

        $priorityData = (new PriorityData())->initFromEntity($priority);
        $priorityData->setValue($priority->getValue() + 1);
        $this->priorityService->update($priority, $priorityData);
    }

    public function decreasePriority(DesireList $desireList, Desire $desire): void
    {
        $priorities = $this->priorityService->findBy(['desireList' => $desireList, 'desire' => $desire]);
        if (count($priorities) !== 1) {
            throw new Exception('No unique Priority found for desire/desireLists combination');
        }
        $priority = $priorities[0];

        $priorityData = (new PriorityData())->initFromEntity($priority);
        $priorityData->setValue($priority->getValue() - 1);
        $this->priorityService->update($priority, $priorityData);
    }

    /**
     * @return Desire[]
     */
    public function findDesiresByListOrderedByPriority(DesireList $list, bool $isForeign = false): array
    {
        return $this->desireService->findByListOrderedByPriority($list, $isForeign);
    }

    public function getDesireListForSecretSantaEvent(User $user, SecretSantaEvent $event): DesireList
    {
        $events = [$event->getFirstRound()];
        if ($event->isIsDoubleRound()) {
            $events[] = $event->getSecondRound();
        }

        $lists = $this->desireListService->findByUserAndEvents(
            $user,
            $events
        );
        if (count($lists) !== 1) {
            if (count($lists) === 2) {
                $lists = $this->desireListService->findByUserAndEvents($user, [$event->getFirstRound()]);
                return $lists[0];
            }

            throw new Exception('No unique desirelist for ss event found');
        }
        return $lists[0];
    }

    /**
     * @param Event[] $events
     * @param string[] $eventRoles
     */
    public function initDesireListsForSecretSantaEvent(
        User $participant,
        SecretSantaEvent $event,
        array $events,
        array $eventRoles
    ): void {
        $eventNames = array_map(
            function (Event $event) {
                return $event->getName();
            },
            $events
        );

        $description = sprintf(
            "%s's Wunschliste für %s. Die Liste wird genutzt für die Events:\n %s",
            $participant->getFirstName(),
            $event->getName(),
            implode(' und ', $eventNames)
        );

        $data = new DesireListData();
        $data->setName('Wunschliste - ' . $event->getName());
        $data->setOwner($participant);
        $data->setDescription($description);
        $data->setEvents($events);
        $data->setDesires([]);

        // check if there is already an desireList for this
        $desireList = null;
        $existingList = $this->desireListService->findByUserAndEvent($participant, $event->getFirstRound());
        if (count($existingList) === 0) {
            $existingList = $this->desireListService->findByUserAndEvent($participant, $event->getSecondRound());
        }

        if (count($existingList) !== 0) {
            $desireList = $existingList[0];
        }

        if ($desireList === null) {
            $desireList = $this->desireListService->createByData($data);
        }

        foreach ($eventRoles as $eventRole) {
            $this->accessRoleService->addRoleToEntity($desireList, $eventRole);
        }
    }

    /**
     * @param string[] $eventRoles
     */
    public function addAccessRolesToDesireList(array $eventRoles, DesireList $desireList): void
    {
        foreach ($eventRoles as $eventRole) {
            $this->accessRoleService->addRoleToEntity($desireList, $eventRole);
        }
    }

    public function initDesireListForEvent(User $owner, Event $event): DesireList
    {
        $description = sprintf(
            "%s's Wunschliste für %s. Die Liste wird genutzt für das Event:\n %s",
            $owner->getFirstName(),
            $event->getName(),
            $event->getName()
        );

        $data = new DesireListData();
        $data->setName('Wunschliste - ' . $event->getName());
        $data->setOwner($owner);
        $data->setDescription($description);
        $data->setEvents([$event]);
        $data->setDesires([]);

        $desireList = $this->desireListService->createByData($data);

        $this->accessRoleService->addRoleToEntity($desireList, 'ROLE_EVENT_' . $event->getId() . '_PARTICIPANT');
        $this->accessRoleService->addRoleToEntity($desireList, 'USER_' . $owner->getId());

        return $desireList;
    }

    public function addReservation(User $user, Desire $desire): Reservation
    {
        if (!$desire->isListed()) {
            throw new Exception('Cant make a reservation for a non listed desire');
        }

        // check reservation is allowed
        $newState = match ($desire->getState()) {
            DesireState::FREE => DesireState::RESERVED,
            DesireState::RESERVED, DesireState::RESOLVED, DesireState::MULTIPLE_RESERVED, DesireState::MULTIPLE_RESOLVED, DesireState::MULTIPLE_RESERVED_OR_RESOLVED => $this->isMultipleReservationAllowed(
                $desire
            ),
        };

        // make reservation
        $reservationData = new ReservationData();
        $reservationData->setDesire($desire);
        $reservationData->setOwner($user);
        $reservationData->setState(ReservationState::RESERVED);
        $reservation = $this->reservationService->createByData($reservationData);

        // update desire
        $desireData = (new DesireData())->initFromEntity($desire);
        $desireData->setState($newState);
        $this->desireService->update($desire, $desireData);

        return $reservation;
    }

    private function isMultipleReservationAllowed(Desire $desire): DesireState
    {
        if ($desire->isExclusive()) {
            throw new Exception('Cant make a second reservation for a non exclusive desire');
        }

        return match ($desire->getState()) {
            DesireState::RESERVED => DesireState::MULTIPLE_RESERVED,
            DesireState::RESOLVED, DesireState::MULTIPLE_RESERVED, DesireState::MULTIPLE_RESOLVED, DesireState::MULTIPLE_RESERVED_OR_RESOLVED => DesireState::MULTIPLE_RESERVED_OR_RESOLVED,
            DesireState::FREE => throw new Exception('To be implemented'),
        };
    }

    public function removeReservation(User $user, Desire $desire): Reservation
    {
        if (!$desire->isListed()) {
            throw new Exception('Cant remove a reservation for a non listed desire');
        }

        // get reservation
        $reservations = $this->reservationService->findBy(['owner' => $user, 'desire' => $desire]);
        if (count($reservations) !== 1) {
            throw new Exception('Non unique reservation found for user desire combination');
        }
        $reservation = $reservations[0];

        if ($reservation->getState() === ReservationState::RESOLVED) {
            throw new Exception('Cant remove an already resolved reservation');
        }

        // check reservation is allowed
        $newState = match ($desire->getState()) {
            DesireState::RESERVED => DesireState::FREE,
            DesireState::MULTIPLE_RESERVED, DesireState::MULTIPLE_RESERVED_OR_RESOLVED => $this->calculateReservationStateAfterRemoval(
                $desire,
                $reservation
            ),
            DesireState::FREE => throw new Exception('Desire with reservation should not have state FREE'),
            DesireState::RESOLVED, DesireState::MULTIPLE_RESOLVED => throw new Exception(
                'Desire seems to have only resolved reservations'
            ),
        };

        // delete reservation
        $this->reservationService->deleteReservation($reservation);

        // update desire
        $desireData = (new DesireData())->initFromEntity($desire);
        $desireData->setState($newState);
        $this->desireService->update($desire, $desireData);

        return $reservation;
    }

    private function calculateReservationStateAfterRemoval(
        Desire $desire,
        Reservation $reservationToRemove
    ): DesireState {
        // should never happen but how knows...
        $reservations = $desire->getReservations();
        if ($reservations->count() === 1) {
            return DesireState::FREE;
        }

        // return the value of the remaining one
        if ($reservations->count() === 2) {
            /** @var Reservation $otherReservation */
            $otherReservations = $reservations->filter(
                function (Reservation $currentReservation) use ($reservationToRemove) {
                    return $currentReservation !== $reservationToRemove;
                }
            );
            $otherReservation = $otherReservations[array_key_first($otherReservations->toArray())];
            return DesireState::from($otherReservation->getState()->value);
        }

        // check if consists only of one state type and use multiple variant if
        $reserved = 0;
        $resolved = 0;

        foreach ($reservations->toArray() as $reservation) {
            if ($reservation->getState() === ReservationState::RESERVED) {
                $reserved = $reserved + 1;
            }
            if ($reservation->getState() === ReservationState::RESOLVED) {
                $resolved = $resolved + 1;
            }
        }

        //  should never happen but how knows... only reserved will remain
        if ($resolved === 1 && $reservationToRemove->getState() === ReservationState::RESOLVED) {
            return DesireState::MULTIPLE_RESERVED;
        }

        // only resolved will remain
        if ($reserved === 1 && $reservationToRemove->getState() === ReservationState::RESERVED) {
            return DesireState::MULTIPLE_RESOLVED;
        }

        // if nothing before fitted it seems to be still multiple of both
        return DesireState::MULTIPLE_RESERVED_OR_RESOLVED;
    }

    public function resolveReservation(User $user, Desire $desire): void
    {
        if (!$desire->isListed()) {
            throw new Exception('Cant remove a reservation for a non listed desire');
        }

        // get reservation
        $reservations = $this->reservationService->findBy(['owner' => $user, 'desire' => $desire]);
        if (count($reservations) !== 1) {
            throw new Exception('Non unique reservation found for user desire combination');
        }
        $reservation = $reservations[0];

        if ($reservation->getState() !== ReservationState::RESERVED) {
            throw new Exception('Cant resolve an already resolved reservation');
        }

        // check reservation is allowed
        $newState = match ($desire->getState()) {
            DesireState::RESERVED => DesireState::RESOLVED,
            DesireState::MULTIPLE_RESERVED => DesireState::MULTIPLE_RESERVED_OR_RESOLVED,
            DesireState::MULTIPLE_RESERVED_OR_RESOLVED => $this->checkAllAreResolved($desire),
            DesireState::FREE => throw new \Exception('Cant be in this state here'),
            DesireState::RESOLVED => throw new \Exception('Cant be in this state here'),
            DesireState::MULTIPLE_RESOLVED => throw new \Exception('Cant be in this state here'),
        };

        // set reservation resolved
        $reservationData = (new ReservationData())->initFromEntity($reservation);
        $reservationData->setState(ReservationState::RESOLVED);
        $this->reservationService->update($reservation, $reservationData);

        // update desire
        $desireData = (new DesireData())->initFromEntity($desire);
        $desireData->setState($newState);
        $this->desireService->update($desire, $desireData);
    }

    private function checkAllAreResolved(Desire $desire): DesireState
    {
        $reservations = $desire->getReservations();

        $reservedOnes = 0;
        foreach ($reservations as $reservation) {
            if ($reservation->getState() === ReservationState::RESERVED) {
                $reservedOnes++;
            }
        }

        if ($reservedOnes > 1) {
            return DesireState::MULTIPLE_RESERVED_OR_RESOLVED;
        }
        return DesireState::MULTIPLE_RESOLVED;
    }


    public function storeDesire(DesireData $data, DesireList $desireList, bool $save = true): void
    {
        $data->setState(DesireState::FREE);

        $desire = $this->desireService->createByData($data);
        $this->desireListService->addDesireToList($desire, $desireList, $save);

        $priorityData = new PriorityData();
        $priorityData->setValue($this->priorityService->getHighestPriorityByList($desireList));
        $priorityData->setDesireList($desireList);
        $priorityData->setDesire($desire);

        $this->priorityService->createByData($priorityData);
    }

    public function updateDesire(DesireData $data, Desire $desire)
    {
        $this->desireService->update($desire, $data);
    }

    public function deleteImageOfDesire(Desire $desire, Image $image)
    {
//        $this->desireService->removeImage($desire, $image);
        $this->imageService->delete($image);
    }

    public function createMasterListForUser(User $user): DesireList
    {
        $data = new DesireListData();
        $data->setName('Globale Wunschliste ' . $user->getFirstName());
        $data->setDescription('Verwende diese Liste um deine Wünsche zentral zu verwalten. Du kannst jeden Wunsch in beliebig viele Listen übernehmen oder kopieren.');
        $data->setOwner($user);
        $data->setEvents([]);
        $data->setDesires([]);
        $data->setAccessRoles([]);
        $data->setMaster(true);

        $desireList = $this->desireListService->createByData($data);
        $this->accessRoleService->addRoleToEntity($desireList, 'USER_' . $user->getId());

        return $desireList;
    }

    public function getMasterListByUser(User $user): DesireList
    {
        $masterList = $this->desireListService->findBy(['owner' => $user, 'master' => true]);
        if (count($masterList) === 0) {
            return $this->createMasterListForUser($user);
        }

        return $masterList[0];
    }

    /**
     * @return array<DesireList>
     */
    public function getNonMasterListsByUser(User $user): array
    {
        return $this->desireListService->findBy(['owner' => $user, 'master' => false]);
    }

    /**
     * @param array<Desire> $desires
     */
    public function hardCopyDesiresBetweenLists(DesireList $targetList, array $desires): void
    {
        foreach ($desires as $desire) {
            $data = (new DesireData())->initFromEntity($desire);

            $this->storeDesire($data, $targetList, false);
        }

        $this->entityManager->flush();
    }

    /**
     * @param array<Desire> $desires
     */
    public function shareDesiresBetweenLists(DesireList $targetList, array $desires): void
    {
        $this->desireListService->shareDesiresBetweenLists($targetList, $desires);
    }
}
