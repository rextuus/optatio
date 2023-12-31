<?php

namespace App\Form;

use App\Content\Event\Data\EventCreateData;
use App\Content\Event\Data\EventData;
use App\Content\Event\EventType;
use App\Entity\Event;
use EasyCorp\Bundle\EasyAdminBundle\Config\Option\TextAlign;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventCreateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class)
            ->add('eventType', EnumType::class, ['class' => EventType::class])
        ;
        $builder->add('submit', SubmitType::class, ['label' => 'Event erstellen']);

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => EventCreateData::class,
        ]);
    }
}
