<?php

declare(strict_types=1);

namespace App\Twig\Components\Event;

use App\Content\Event\EventManager;
use App\Entity\Event;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent()]
class EventComponent extends AbstractController
{
    use DefaultActionTrait;

    public User $user;
    public Event $event;

    public function isUserParticipant(): bool
    {
        return in_array($this->user, $this->event->getParticipants()->toArray());
    }

    public function joinDisabled(): string
    {
        if ($this->isUserParticipant()) {
            return 'disabled';
        }
        return '';
    }

    public function exitDisabled(): string
    {
        if (!$this->isUserParticipant()) {
            return 'disabled';
        }
        return '';
    }


    public function canEdit(): bool
    {
        return in_array(
            EventManager::getEventOwnerRole($this->event),
            $this->user->getAccessRoles()->toArray()
        );
    }

    public function getHeaderText(): string
    {
        if ($this->canEdit()) {
            return 'Dies ist dein Event';
        }

        if ($this->isUserParticipant()) {
            return 'Du nimmst bereits teil';
        }

        return 'Das ist ein Event von ' . $this->event->getCreator()->getFirstName();
    }

}
