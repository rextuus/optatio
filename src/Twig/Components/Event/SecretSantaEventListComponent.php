<?php

namespace App\Twig\Components\Event;

use App\Content\Event\EventInterface;
use App\Entity\Event;
use App\Entity\SecretSantaEvent;
use App\Entity\User;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class SecretSantaEventListComponent
{
    use DefaultActionTrait;

    /**
     * @var array<SecretSantaEvent>
     */
    #[LiveProp]
    public array $events = [];

    #[LiveProp]
    public ?User $user = null;
}
