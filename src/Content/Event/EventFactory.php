<?php
declare(strict_types=1);

namespace App\Content\Event;

use App\Content\Event\Data\EventData;
use App\Entity\Event;


class EventFactory
{
    public function createByData(EventData $data): Event
    {
        $event = $this->createNewInstance();
        $this->mapData($data, $event);
        return $event;
    }

    public function mapData(EventData $data, Event $event): Event
    {
        $event->setName($data->getName());
        $event->setCreator($data->getCreator());
        $event->setOpenToJoin($data->isOpenToJoin());
        $event->setEventType($data->getEventType());
        $event->setAccessRoles($data->getAccessRoles());

        foreach ($data->getParticipants() as $participant) {
            $event->addParticipant($participant);
        }

        return $event;
    }

    private function createNewInstance(): Event
    {
        return new Event();
    }
}
