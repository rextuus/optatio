<?php

namespace App\Twig\Components;

use App\Content\Desire\DesireManager;
use App\Content\Desire\DesireRepository;
use App\Content\DesireList\Relation\DesireListRelationFactory;
use App\Content\DesireList\Relation\DesireListRelationRepository;
use App\Content\DesireList\Relation\DesireListRelationType;
use App\Content\Priority\Data\PriorityChangeData;
use App\Content\Priority\Data\PriorityChangeType;
use App\Entity\Desire;
use App\Entity\DesireList;
use App\Entity\DesireListRelation;
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

use function PHPUnit\Framework\matches;

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

    #[LiveProp]
    public ?DesireListRelation $desireListRelation = null;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly DesireManager $desireManager,
        private readonly DesireListRelationRepository $desireListRelationRepository,
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

    public function isActive(): bool
    {
        return $this->desire->isListed();
    }

    public function isExclusive(): bool
    {
        return $this->desire->isExclusive();
    }

    public function isExactly(): bool
    {
        return $this->desire->isExactly();
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

    public function getRelationIcon(): string
    {
        $relationIcon = '';

        $relationType = $this->getDesireListRelationType();
        if ($relationType !== null) {
            match ($relationType) {
                DesireListRelationType::SHARED => $relationIcon = '<i class="fa-solid fa-arrows-spin text-warning fa-lg"></i>',
                DesireListRelationType::COPIED => $relationIcon = '<i class="fa-solid fa-arrows-left-right text-warning fa-lg"></i>',
                DesireListRelationType::MOVED => $relationIcon = '<i class="fa-solid fa-arrow-circle-right text-warning fa-lg"></i>',
            };
        }

        return $relationIcon;
    }

    public function getRelationInfo(): ?string
    {
        $relation = $this->getRelation();

        if ($relation === null) {
            return null;
        }

        $message = null;
        if ($relation->getRelationType() === DesireListRelationType::SHARED) {
            $message = sprintf(
                'Du hast diesen Wunsch geteilt aus der Liste %s. Änderst du ihn hier, wird er auch dort geändert.',
                $relation->getSourceList()->getName()
            );
        }

        if ($relation->getRelationType() === DesireListRelationType::COPIED) {
            $message = sprintf(
                'Du hast diesen Wunsch kopiert aus der Liste %s. Änderungen beeinflussen nur diese Variante',
                $relation->getSourceList()->getName()
            );
        }

        if ($relation->getRelationType() === DesireListRelationType::MOVED) {
            $message = sprintf(
                'Du hast diesen Wunsch aus der Liste %s hierher verschoben',
                $relation->getSourceList()->getName()
            );
        }

        return $message;
    }

    private function getDesireListRelationType(): ?DesireListRelationType
    {
        return $this->getRelation()?->getRelationType();
    }

    private function getRelation(): ?DesireListRelation
    {
        if ($this->desireListRelation !== null){
            return $this->desireListRelation;
        }

        $relations = $this->desireListRelationRepository->getRelationsForDesireAndTargetList(
            $this->desire,
            $this->desireList
        );

        if (count($relations) > 0) {
            $relation = $relations[array_key_first($relations)];
            $this->desireListRelation = $relation;

            return $relation;
        }

        return null;
    }
}
