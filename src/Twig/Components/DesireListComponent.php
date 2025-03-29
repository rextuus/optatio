<?php

namespace App\Twig\Components;

use App\Content\Desire\DesireManager;
use App\Entity\Desire;
use App\Entity\DesireList;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class DesireListComponent
{
    use DefaultActionTrait;

    /**
     * @var array<Desire>
     */
    #[LiveProp]
    public array $desires = [];

    #[LiveProp]
    public ?DesireList $list = null;

    #[LiveProp(writable: true)]
    public ?int $desireCount = 1;

    public function __construct(private readonly DesireManager $desireManager)
    {
    }

    #[LiveListener('priorityChanged')]
    public function priorityChanged(): void
    {
        // we update the desireCount only to provoke the re-rendering of the listing component
        $this->desireCount = count($this->desires);
        $this->calculateDesireOrder();
        $this->cleanPriorities();
    }

    private function calculateDesireOrder(): void
    {
        $list = $this->list;
        usort($this->desires, function (Desire $a, Desire $b) use ($list) {
            return $a->getPriorityByList($list)->getValue() <=> $b->getPriorityByList($list)->getValue();
        });
    }

    private function cleanPriorities(): void
    {
        foreach ($this->desires as $index => $desire) {
            $this->desireManager->setPriority($this->list, $desire, $index + 1);
        }
    }
}
