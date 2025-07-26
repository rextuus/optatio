<?php
declare(strict_types=1);

namespace App\Content\SecretSanta\Secret\Data;

use App\Entity\Event;
use App\Entity\Secret;
use App\Entity\SecretSantaEvent;
use App\Entity\User;


class SecretData
{
    private SecretSantaEvent $secretSantaEvent;

    private Event $event;

    private User $provider;

    private User $receiver;

    private bool $retrieved;

    public function getSecretSantaEvent(): SecretSantaEvent
    {
        return $this->secretSantaEvent;
    }

    public function setSecretSantaEvent(SecretSantaEvent $secretSantaEvent): SecretData
    {
        $this->secretSantaEvent = $secretSantaEvent;
        return $this;
    }

    public function getEvent(): Event
    {
        return $this->event;
    }

    public function setEvent(Event $event): SecretData
    {
        $this->event = $event;
        return $this;
    }

    public function getProvider(): User
    {
        return $this->provider;
    }

    public function setProvider(User $provider): SecretData
    {
        $this->provider = $provider;
        return $this;
    }

    public function getReceiver(): User
    {
        return $this->receiver;
    }

    public function setReceiver(User $receiver): SecretData
    {
        $this->receiver = $receiver;
        return $this;
    }

    public function isRetrieved(): bool
    {
        return $this->retrieved;
    }

    public function setRetrieved(bool $retrieved): SecretData
    {
        $this->retrieved = $retrieved;
        return $this;
    }

    public function initFromEntity(Secret $secret): SecretData
    {
        $this->setProvider($secret->getProvider());
        $this->setReceiver($secret->getReceiver());
        $this->setEvent($secret->getEvent());
        $this->setSecretSantaEvent($secret->getSecretSantaEvent());
        $this->setRetrieved($secret->isRetrieved());

        return $this;
    }
}
