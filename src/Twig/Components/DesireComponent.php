<?php

namespace App\Twig\Components;

use App\Content\Desire\DesireManager;
use App\Content\Priority\Data\PriorityChangeData;
use App\Content\Priority\Data\PriorityChangeType;
use App\Entity\Desire;
use App\Entity\DesireList;
use App\Entity\Priority;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class DesireComponent extends AbstractController
{
    use DefaultActionTrait;
    use ComponentToolsTrait;
    use ComponentWithFormTrait;

    #[LiveProp(writable: true)]
    public int $priority = 0;

    #[LiveProp(writable: true)]
    public ?Desire $desire = null;

    #[LiveProp(writable: true)]
    public ?DesireList $desireList = null;

    #[LiveProp(writable: true)]
    public int $current = 0;

    #[LiveProp]
    public bool $disableUp = false;
    #[LiveProp]
    public bool $disableDown = false;

    #[LiveProp]
    public ?PriorityChangeData $initialFormData = null;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly DesireManager $desireManager
    ) {
    }

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(PriorityChangeType::class, (new PriorityChangeData())->setPriority($this->current));
    }

    #[LiveAction]
    public function save(): void
    {
        $this->submitForm();
        $data = $this->getForm()->getData();

        // we do this to set the exact position after recalculation: see DesireListComponent::priorityChanged
        $newPriority = $data->getPriority();
        if ($newPriority < $this->current) {
            $newPriority--;
        } elseif ($newPriority > $this->current) {
            $newPriority++;
        }

        $this->desireManager->setPriority($this->desireList, $this->desire, $newPriority);

        // rerender parent using event
        $this->emit('priorityChanged');
    }

    #[LiveAction]
    public function move(#[LiveArg] string $direction): void
    {
        // we do this to set the exact position after recalculation: see DesireListComponent::priorityChanged
        $newPriority = $this->current;
        if ($direction === 'up') {
            $newPriority = $newPriority - 2;
        } elseif ($direction === 'down') {
            $newPriority = $newPriority + 2;
        }
//dd($newPriority);
        $this->desireManager->setPriority($this->desireList, $this->desire, $newPriority);

        // rerender parent using event
        $this->emit('priorityChanged');
    }

    public function isListed(): string
    {
        if ($this->desire->isListed()) {
            return '';
        }
        return 'inactive-card';
    }

    public function isActive(): string
    {
        if ($this->desire->isListed()) {
            return 'text-success';
        }
        return 'text-danger';
    }

    public function isExclusive(): string
    {
        if ($this->desire->isExclusive()) {
            return 'text-success';
        }
        return 'text-danger';
    }

    public function isExactly(): string
    {
        if ($this->desire->isExactly()) {
            return 'text-success';
        }
        return 'text-danger';
    }

    public function getDisableUp(): string
    {
        if ($this->disableUp) {
            return 'disabled-desire-priority';
        }
        return '';
    }

    public function getDisableDown(): string
    {
        if ($this->disableDown) {
            return 'disabled-desire-priority';
        }
        return '';
    }

    public function isDown(): string
    {
        if ($this->disableDown) {
            return 'text-secondary';
        }
        return 'text-danger';
    }

    public function isUp(): string
    {
        if ($this->disableUp) {
            return 'text-secondary';
        }
        return 'text-success';
    }
}
