<?php
declare(strict_types=1);

namespace App\Content\Event;

use App\Content\Event\Data\EventCreateData;
use App\Content\Event\Data\EventData;
use App\Entity\Event;
use App\Entity\User;
use Symfony\Component\Security\Core\User\UserInterface;


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

    /**
     * @return Event[]
     */
    public function findEventsWithoutSecretSantaRounds(?User $user = null): array
    {
        return $this->repository->findEventsWithoutSecretSantaRounds($user);
    }

    public function findEventsForUser(User $user)
    {
        return $this->repository->findEventsForUser($user);
    }

    public function save(Event $event){
        $this->repository->save($event);
    }
}
