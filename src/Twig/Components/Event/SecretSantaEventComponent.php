<?php

declare(strict_types=1);

namespace App\Twig\Components\Event;

use App\Content\Desire\DesireService;
use App\Content\Event\EventManager;
use App\Entity\Desire;
use App\Entity\Event;
use App\Entity\SecretSantaEvent;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent()]
class SecretSantaEventComponent extends AbstractController
{
    use DefaultActionTrait;

    public User $user;
    public SecretSantaEvent $event;

    public function __construct(private readonly DesireService $desireService)
    {
    }


    public function isUserParticipant(): bool
    {
        return in_array($this->user, $this->event->getOverallParticipants());
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
            EventManager::getEventOwnerRole($this->event->getFirstRound()),
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

    public function getTotalDesireCount(): int
    {
        return count($this->desireService->getAllDesiresForSecretSantaEvent($this->event));
    }
}
