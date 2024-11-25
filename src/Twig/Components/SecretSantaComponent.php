<?php

namespace App\Twig\Components;

use App\Content\Event\EventManager;
use App\Entity\SecretSantaEvent;
use App\Entity\User;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\DefaultActionTrait;

use function Symfony\Component\String\b;

#[AsLiveComponent()]
final class SecretSantaComponent
{
    use DefaultActionTrait;

    public SecretSantaEvent $event;
    public User $user;

    public function isUserParticipantFirstRound(): bool
    {
        return in_array($this->user, $this->event->getFirstRound()->getParticipants()->toArray());
    }

    public function isUserParticipantSecondRound(): bool
    {
        if (!$this->event->isIsDoubleRound()){
            return false;
        }

        return in_array($this->user, $this->event->getSecondRound()->getParticipants()->toArray());
    }

    public function disabled(): string
    {
        if ($this->isUserParticipantFirstRound() && $this->isUserParticipantSecondRound()){
            return 'disabled';
        }
        return '';
    }

    public function isAlreadyParticipant(): bool
    {
        if ($this->isUserParticipantFirstRound() || $this->isUserParticipantSecondRound()){
            return true;
        }
        return false;
    }

    public function canEdit(): bool
    {
        return in_array(EventManager::getEventOwnerRole($this->event->getFirstRound()), $this->user->getRoles());
    }
}
