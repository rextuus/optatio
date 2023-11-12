<?php
declare(strict_types=1);

namespace App\Content\Event;

use App\Content\Event\Data\EventCreateData;
use App\Content\Event\Data\EventData;
use App\Entity\Event;
use App\Entity\User;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @author Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2023 DocCheck Community GmbH
 */
class EventService
{
    public function __construct(private readonly EventRepository $repository, private readonly EventFactory $factory)
    {
    }

    public function createByData(EventData $data): Event
    {
        $event = $this->factory->createByData($data);
        $this->repository->save($event);
        return $event;
    }

    public function update(Event $event, EventData $data): Event
    {
        $event = $this->factory->mapData($data, $event);
        $this->repository->save($event);
        return $event;
    }

    /**
     * @return Event[]
     */
    public function findBy(array $conditions): array
    {
        return $this->repository->findBy($conditions);
    }

    public function initEvent(EventCreateData $createData, User $user): void
    {
        $data = new EventData();
        $data->setName($createData->getName());
        $data->setCreator($user);
        $data->setOpenToJoin($createData->getEventType()->shouldBeOpen());
        $data->setEventType($createData->getEventType());

        $event = $this->createByData($data);
        $data = (new EventData())->initFromEntity($event);
        $data->setAccessRoles(
            [
                'ROLE_USER',
                'ROLE_EVENT_' . $event->getId() . '_OWNER',
                'ROLE_EVENT_' . $event->getId() . '_PARTICIPANT'
            ]
        );

        $this->factory->mapData($data, $event);
        $this->repository->save($event);
    }

    public function findEventsForUser(User $user)
    {
        return $this->repository->findEventsForUser($user);
    }

    public function addParticipant(Event $event, User $participant): void
    {
        if (!in_array($participant, $event->getParticipants()->toArray())){
            $event->addParticipant($participant);
            $this->repository->save($event);
        }
    }
}
