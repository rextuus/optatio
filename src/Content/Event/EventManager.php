<?php
declare(strict_types=1);

namespace App\Content\Event;

use App\Content\Desire\DesireManager;
use App\Content\DesireList\DesireListService;
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


class EventManager
{

    public function __construct(
        private readonly EventService $eventService,
        private readonly DesireManager $desireManager,
        private readonly UserService $userService,
        private readonly SecretSantaEventService $secretSantaEventService,
        private readonly DesireListService $desireListService
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
        $isDoubleRound = $data->getSecondRoundName() !== null;

        // init events
        $eventData = new EventCreateData();
        $eventData->setName($data->getFirstRoundName());
        $eventData->setEventType(EventType::SECRET_SANTA);
        $eventFirst = $this->initEvent($eventData, $creator);

        $createData = new SecretSantaEventData();
        $createData->setName($data->getName());
        $createData->setCreator($creator);
        $createData->setSecretSantaState(SecretSantaState::OPEN);
        $createData->setFirstRound($eventFirst);
        $createData->setIsDoubleRound($isDoubleRound);

        if ($isDoubleRound){
            $eventData = new EventCreateData();
            $eventData->setName($data->getSecondRoundName());
            $eventData->setEventType(EventType::SECRET_SANTA);
            $eventSecond = $this->initEvent($eventData, $creator);
            $createData->setSecondRound($eventSecond);
        }

        return $this->secretSantaEventService->createByData($createData);
    }

    public function addParticipantToSecretSantaEvent(User $participant, SecretSantaEvent $ssEvent, SecretSantaEventJoinData $data): void
    {
        $events = [];

        if ($data->isFirstRound() && !$ssEvent->getFirstRound()->getParticipants()->contains($participant)){
            $this->addParticipant($ssEvent->getFirstRound(), $participant);
            $events[] = $ssEvent->getFirstRound();
        }
        if ($data->isSecondRound() && $ssEvent->getSecondRound() !== null && !$ssEvent->getSecondRound()->getParticipants()->contains($participant)){
            $this->addParticipant($ssEvent->getSecondRound(), $participant);
            $events[] = $ssEvent->getSecondRound();
        }

        // create default desire list so that each user of the two ss events can see
        $eventRoles = array_map(
            function (Event $event){
                return 'ROLE_EVENT_'.$event->getId().'_PARTICIPANT';
            },
            $events
        );
        $eventRoles[] = 'USER_'.$participant->getId();

        $this->desireManager->initDesireListsForSecretSantaEvent($participant, $ssEvent, $events, $eventRoles);
    }

    // Was created to fix if user need added to second round later
    public function fixUser(User $participant, SecretSantaEvent $ssEvent, SecretSantaEventJoinData $data): void
    {
        if ($data->isFirstRound()){
            $this->addParticipant($ssEvent->getFirstRound(), $participant);
            $events[] = $ssEvent->getFirstRound();
        }
        if ($data->isSecondRound() && $ssEvent->getSecondRound() !== null){
            $this->addParticipant($ssEvent->getSecondRound(), $participant);
            $events[] = $ssEvent->getSecondRound();
        }

        $eventRoles = array_map(
            function (Event $event){
                return 'ROLE_EVENT_'.$event->getId().'_PARTICIPANT';
            },
            $events
        );
        $eventRoles[] = 'USER_'.$participant->getId();

        $lists = $this->desireListService->findByUserAndEvent($participant, $ssEvent->getFirstRound());
        if(count($lists) === 0){
            $lists = $this->desireListService->findByUserAndEvent($participant, $ssEvent->getSecondRound());
        }
        if(count($lists) === 0){
            throw new \Exception('No desire list found');
        }
        $list = $lists[0];

        $this->desireManager->addAccessRolesToDesireList($eventRoles, $list);

    }

    public function fixDesireList(User $participant, SecretSantaEvent $ssEvent, SecretSantaEventJoinData $data): void
    {
        if ($data->isFirstRound()){
//            $this->addParticipant($ssEvent->getFirstRound(), $participant);
            $events[] = $ssEvent->getFirstRound();
        }
        if ($data->isSecondRound() && $ssEvent->getSecondRound() !== null){
//            $this->addParticipant($ssEvent->getSecondRound(), $participant);
            $events[] = $ssEvent->getSecondRound();
        }

        $eventRoles = array_map(
            function (Event $event){
                return 'ROLE_EVENT_'.$event->getId().'_PARTICIPANT';
            },
            $events
        );
        $eventRoles[] = 'USER_'.$participant->getId();

        $this->desireManager->initDesireListsForSecretSantaEvent($participant, $ssEvent, $events, $eventRoles);
    }

    public function initBirthdayEvent(EventCreateData $data, User $creator): Event
    {
        $event = $this->initEvent($data, $creator);
        $this->desireManager->initDesireListForEvent($creator, $event);

        return $event;
    }
}
