<?php

namespace App\Form;

use App\Content\SecretSanta\SecretSantaEvent\Data\SecretSantaEventJoinData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SecretSantaEventJoinType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstRound', CheckboxType::class, ['required' => false])
            ->add('secondRound', CheckboxType::class, ['required' => false])
            ->add('submit', SubmitType::class, ['label' => 'Mitmachen'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SecretSantaEventJoinData::class
        ]);
    }
}
