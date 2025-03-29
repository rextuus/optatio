<?php

namespace App\Twig\Components;

use App\Content\Desire\ActionType;
use App\Content\Desire\DesireManager;
use App\Content\DesireList\Data\DesireCopyData;
use App\Entity\User;
use App\Form\DesireCopyType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
class DesireCopyForm extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;

    #[LiveProp(writable: true, useSerializerForHydration: true, serializationContext: ['groups' => ['live_component']])]
    public ?DesireCopyData $initialFormData = null;

    #[LiveProp]
    public ?User $user = null;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly DesireManager $desireManager,
    ) {
    }

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(
            DesireCopyType::class,
            $this->initialFormData,
            ['user' => $this->user, 'form_data' => $this->initialFormData]
        );
    }

    #[LiveAction]
    public function submit(): void
    {
        $this->submitForm();

        /** @var DesireCopyData $copyData */
        $copyData = $this->getForm()->getData();

        match ($copyData->getAction()) {
            ActionType::TEILEN => $this->desireManager->shareDesiresBetweenLists(
                $copyData->getTo(),
                $copyData->getDesires()
            ),
            ActionType::KOPIEREN => $this->desireManager->hardCopyDesiresBetweenLists(
                $copyData->getTo(),
                $copyData->getDesires()
            ),
            ActionType::VERSCHIEBEN => $this->desireManager->switchDesireBetweenLists(
                $copyData->getFrom(),
                $copyData->getTo(),
                $copyData->getDesires()
            ),
        };
    }
}
