<?php

namespace App\Twig\Components;

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

#[AsLiveComponent()]
class DesireCopyForm extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;

    #[LiveProp(writable: true)]
    public ?DesireCopyData $initialFormData = null;

    #[LiveProp]
    public ?User $user = null;

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(DesireCopyType::class, $this->initialFormData, ['user' => $this->user]);
    }

    #[LiveAction]
    public function save(EntityManagerInterface $entityManager)
    {
        // Submit the form! If validation fails, an exception is thrown
        // and the component is automatically re-rendered with the errors

//        $this->submitForm();

        $form = $this->form;

        // Perform form validation and logic
        if ($form->isSubmitted() && $form->isValid()) {
            // Form is valid, process the data
            $data = $form->getData();
            // ... (handle the data, save to database, etc.)

            // Optionally, add a flash message or handle response
            $this->addFlash('success', 'Form saved successfully!');
        } else {
            // Handle form errors
            $errors = $form->getErrors();
            // ... (handle the errors, update UI, etc.)
        }
    }
}
