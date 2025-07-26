<?php
declare(strict_types=1);

namespace App\Content\Event\Data;

use App\Content\Event\EventType;
use App\Entity\Event;
use App\Entity\User;


class EventData
{
    private string $name;

    /**
     * @var User[]
     */
    private array $participants = [];
    private User $creator;

    private EventType $eventType;

    private bool $openToJoin;

    /**
     * @var string[]
     */
    private array $accessRoles = [];

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): EventData
    {
        $this->name = $name;
        return $this;
    }

    public function getParticipants(): array
    {
        return $this->participants;
    }

    public function setParticipants(array $participants): EventData
    {
        $this->participants = $participants;
        return $this;
    }

    public function getCreator(): User
    {
        return $this->creator;
    }

    public function setCreator(User $creator): EventData
    {
        $this->creator = $creator;
        return $this;
    }

    public function getEventType(): EventType
    {
        return $this->eventType;
    }

    public function setEventType(EventType $eventType): EventData
    {
        $this->eventType = $eventType;
        return $this;
    }

    public function isOpenToJoin(): bool
    {
        return $this->openToJoin;
    }

    public function setOpenToJoin(bool $openToJoin): EventData
    {
        $this->openToJoin = $openToJoin;
        return $this;
    }

    public function getAccessRoles(): array
    {
        return $this->accessRoles;
    }

    public function setAccessRoles(array $accessRoles): EventData
    {
        $this->accessRoles = $accessRoles;
        return $this;
    }

    public function initFromEntity(Event $event): EventData
    {
        $this->setName($event->getName());
        $this->setCreator($event->getCreator());
        $this->setParticipants($event->getParticipants()->toArray());
        $this->setOpenToJoin($event->isOpenToJoin());
        $this->setEventType($event->getEventType());
        $this->setAccessRoles($event->getAccessRoles());

        return $this;
    }
}
