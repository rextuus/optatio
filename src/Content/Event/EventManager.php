<?php
declare(strict_types=1);

namespace App\Content\Event;

use App\Content\Event\Data\EventCreateData;
use App\Content\Event\Data\EventData;
use App\Content\SecretSanta\SecretSantaEvent\Data\SecretSantaCreateData;
use App\Content\SecretSanta\SecretSantaEvent\Data\SecretSantaEventData;
use App\Content\SecretSanta\SecretSantaEvent\SecretSantaEventService;
use App\Content\SecretSanta\SecretSantaState;
use App\Content\User\UserService;
use App\Entity\Event;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @author Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2023 DocCheck Community GmbH
 */
class EventManager
{

    public function __construct(
        private EventService $eventService,
        private UserService $userService,
        private SecretSantaEventService $secretSantaEventService,
    )
    {

    }

    public function initEvent(EventCreateData $createData, User $user): Event
    {
        $data = new EventData();
        $data->setName($createData->getName());
        $data->setCreator($user);
        $data->setOpenToJoin($createData->getEventType()->shouldBeOpen());
        $data->setEventType($createData->getEventType());

        $event = $this->eventService->createByData($data);
        $data = (new EventData())->initFromEntity($event);
        $data->setAccessRoles(
            [
                'ROLE_USER',
                'ROLE_EVENT_' . $event->getId() . '_OWNER',
                'ROLE_EVENT_' . $event->getId() . '_PARTICIPANT'
            ]
        );

        $this->eventService->update($event, $data);

        // TODO give use role
        $this->userService->addEventOwnerRoleToUser($event, $user);

        return $event;
    }

    public static function getEventOwnerRole(Event $event): string
    {
        return 'ROLE_EVENT_' . $event->getId() . '_OWNER';
    }

    public static function getEventRole(Event $event): string
    {
        return 'ROLE_EVENT_' . $event->getId() . '_PARTICIPANT';
    }

    public function addParticipant(Event $event, User $participant): void
    {
        if (!in_array($participant, $event->getParticipants()->toArray())){
            $event->addParticipant($participant);
            $this->eventService->save($event);
        }

        // TODO give use role
        $this->userService->addEventRoleToUser($event, $participant);
    }



    public function removeParticipant(Event $event, User $participant): void
    {
        if (in_array($participant, $event->getParticipants()->toArray())){
            $event->removeParticipant($participant);
            $this->eventService->save($event);
        }

        // TODO give use role
        $this->userService->removeEventRoleToUser($event, $participant);
    }

    public function initSecretSantaEvent(SecretSantaCreateData $data, User $creator)
    {
        // init events
        $eventData = new EventCreateData();
        $eventData->setName($data->getNameFirst());
        $eventData->setEventType(EventType::SECRET_SANTA);
        $eventFirst = $this->initEvent($eventData, $creator);

        $eventData = new EventCreateData();
        $eventData->setName($data->getNameSecond());
        $eventData->setEventType(EventType::SECRET_SANTA);
        $eventSecond = $this->initEvent($eventData, $creator);


        $createData = new SecretSantaEventData();
        $createData->setName($data->getName());
        $createData->setCreator($creator);
        $createData->setSecretSantaState(SecretSantaState::OPEN);
        $createData->setFirstRound($eventFirst);
        $createData->setSecondRound($eventSecond);

        $this->secretSantaEventService->createByData($createData);
    }
}
