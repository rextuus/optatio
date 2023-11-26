<?php
declare(strict_types=1);

namespace App\Content\Event;

use App\Content\Desire\DesireManager;
use App\Content\Event\Data\EventCreateData;
use App\Content\Event\Data\EventData;
use App\Content\SecretSanta\SecretSantaEvent\Data\SecretSantaEventCreateData;
use App\Content\SecretSanta\SecretSantaEvent\Data\SecretSantaEventJoinData;
use App\Content\SecretSanta\SecretSantaEvent\Data\SecretSantaEventData;
use App\Content\SecretSanta\SecretSantaEvent\SecretSantaEventService;
use App\Content\SecretSanta\SecretSantaState;
use App\Content\User\UserService;
use App\Entity\Event;
use App\Entity\SecretSantaEvent;
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
        private DesireManager $desireManager,
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

    public function initSecretSantaEvent(SecretSantaEventCreateData $data, User $creator): SecretSantaEvent
    {
        // init events
        $eventData = new EventCreateData();
        $eventData->setName($data->getFirstRoundName());
        $eventData->setEventType(EventType::SECRET_SANTA);
        $eventFirst = $this->initEvent($eventData, $creator);

        $eventData = new EventCreateData();
        $eventData->setName($data->getSecondRoundName());
        $eventData->setEventType(EventType::SECRET_SANTA);
        $eventSecond = $this->initEvent($eventData, $creator);


        $createData = new SecretSantaEventData();
        $createData->setName($data->getName());
        $createData->setCreator($creator);
        $createData->setSecretSantaState(SecretSantaState::OPEN);
        $createData->setFirstRound($eventFirst);
        $createData->setSecondRound($eventSecond);

        return $this->secretSantaEventService->createByData($createData);
    }

    public function addParticipantToSecretSantaEvent(User $participant, SecretSantaEvent $event, SecretSantaEventJoinData $data): void
    {
        $events = [];
        if ($data->isFirstRound()){
            $this->addParticipant($event->getFirstRound(), $participant);
            $events[] = $event->getFirstRound();
        }
        if ($data->isSecondRound()){
            $this->addParticipant($event->getSecondRound(), $participant);
            $events[] = $event->getSecondRound();
        }

        // create default desire list so that each user of the two ss events can see
        $eventRoles = array_map(
            function (Event $event){
                return 'ROLE_EVENT_'.$event->getId().'_PARTICIPANT';
            },
            [$event->getFirstRound(), $event->getSecondRound()]
        );
        $eventRoles[] = 'USER_'.$participant->getId();

        $debug = array_map(
            function (Event $event){
                return $event->getName();
            },
            $events
        );

        dump('adde '.$participant->getFullName().' to '.implode(', ',$debug));
        $this->desireManager->initDesireListsForSecretSantaEvent($participant, $event, $events, $eventRoles);

//        $this->userService->addRolesToUser($participant, $eventRoles);
    }
}
