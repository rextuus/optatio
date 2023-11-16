<?php
declare(strict_types=1);

namespace App\Twig\Components;

use App\Content\Event\EventManager;
use App\Entity\Event;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

/**
 * @author Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2023 DocCheck Community GmbH
 */
#[AsLiveComponent()]
class EventComponent extends AbstractController
{
    use DefaultActionTrait;

    public User $user;
    public Event $event;
    public bool $hasEditButton;

    public function isUserParticipant(): bool
    {
        return in_array($this->user, $this->event->getParticipants()->toArray());
    }

    public function joinDisabled(): string
    {
        if ($this->isUserParticipant()){
            return 'disabled';
        }
        return '';
    }

    public function exitDisabled(): string
    {
        if (!$this->isUserParticipant()){
            return 'disabled';
        }
        return '';
    }


    public function canEdit(): bool
    {
        return $this->hasEditButton && in_array(EventManager::getEventOwnerRole($this->event), $this->user->getRoles());
    }
}
