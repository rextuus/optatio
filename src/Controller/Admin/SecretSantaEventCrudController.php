<?php

namespace App\Controller\Admin;

use App\Content\Event\EventType;
use App\Content\SecretSanta\SecretSantaState;
use App\Entity\SecretSantaEvent;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class SecretSantaEventCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return SecretSantaEvent::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('name'),
            ChoiceField::new('state')->setChoices(SecretSantaState::cases()),
        ];
    }

}
