<?php
declare(strict_types=1);

namespace App\Content\SecretSanta\SecretSantaEvent\Data;

use App\Content\SecretSanta\SecretSantaState;
use App\Entity\Event;
use App\Entity\SecretSantaEvent;
use App\Entity\User;

/**
 * @author Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2023 DocCheck Community GmbH
 */
class SecretSantaEventData
{
    private string $name;
    private Event $firstRound;
    private Event $secondRound;
    private SecretSantaState $secretSantaState;
    private User $creator;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): SecretSantaEventData
    {
        $this->name = $name;
        return $this;
    }

    public function getFirstRound(): Event
    {
        return $this->firstRound;
    }

    public function setFirstRound(Event $firstRound): SecretSantaEventData
    {
        $this->firstRound = $firstRound;
        return $this;
    }

    public function getSecondRound(): Event
    {
        return $this->secondRound;
    }

    public function setSecondRound(Event $secondRound): SecretSantaEventData
    {
        $this->secondRound = $secondRound;
        return $this;
    }

    public function getSecretSantaState(): SecretSantaState
    {
        return $this->secretSantaState;
    }

    public function setSecretSantaState(SecretSantaState $secretSantaState): SecretSantaEventData
    {
        $this->secretSantaState = $secretSantaState;
        return $this;
    }

    public function getCreator(): User
    {
        return $this->creator;
    }

    public function setCreator(User $creator): SecretSantaEventData
    {
        $this->creator = $creator;
        return $this;
    }

    public function initFromEntity(SecretSantaEvent $event):SecretSantaEventData
    {
        $this->setName($event->getName());
        $this->setCreator($event->getCreator());
        $this->setFirstRound($event->getFirstRound());
        $this->setSecondRound($event->getSecondRound());
        $this->setSecretSantaState($event->getState());

        return $this;
    }
}
