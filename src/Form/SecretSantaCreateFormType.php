<?php

namespace App\Form;

use App\Content\SecretSanta\SecretSantaEvent\Data\SecretSantaEventCreateData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SecretSantaCreateFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, ['label' => 'Event-Name'])
            ->add('firstRoundName', TextType::class)
            ->add('enableSecondRound', CheckboxType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Enable Second Round',
                'attr' => [
                    'data-action' => 'change->secret-santa-form#toggle'
                ]
            ])
            ->add('secondRoundName', TextType::class, [
                'required' => false,
            ])
            ->add('submit', SubmitType::class, ['label' => 'Erstellen']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SecretSantaEventCreateData::class,
        ]);
    }
}