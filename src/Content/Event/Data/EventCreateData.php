<?php
declare(strict_types=1);

namespace App\Content\Event\Data;

use App\Content\Event\EventType;
use Symfony\Component\VarDumper\Cloner\Data;


class EventCreateData
{
    private string $name;
    private EventType $eventType;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): EventCreateData
    {
        $this->name = $name;
        return $this;
    }

    public function getEventType(): EventType
    {
        return $this->eventType;
    }

    public function setEventType(EventType $eventType): EventCreateData
    {
        $this->eventType = $eventType;
        return $this;
    }
}
