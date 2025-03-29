<?php

namespace App\Twig\Components;

use App\Content\Desire\DesireManager;
use App\Entity\DesireList;
use App\Entity\Event;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class EventDetail
{
    use DefaultActionTrait;

    #[LiveProp]
    public ?Event $event = null;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[LiveAction]
    public function togglePriority(): void
    {
        $desireList = $this->event->getDesireLists()->get(0);
        $desireList->setHasPriority(!$desireList->hasPriority());

        $this->entityManager->persist($desireList);
        $this->entityManager->flush();
    }
}
