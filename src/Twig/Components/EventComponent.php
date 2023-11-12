<?php
declare(strict_types=1);

namespace App\Twig\Components;

use App\Entity\Event;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

/**
 * @author Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2023 DocCheck Community GmbH
 */
#[AsTwigComponent]
class EventComponent extends AbstractController
{
    public User $user;
    public Event $event;

    public function isUserParticipant(): bool
    {
        return in_array($this->user, $this->event->getParticipants()->toArray());
    }

    public function disabled(): string
    {
        if ($this->isUserParticipant()){
            return 'disabled';
        }
        return '';
    }
}
