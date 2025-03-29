<?php

declare(strict_types=1);

namespace App\Content\Priority\Data;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PriorityChangeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('priority', IntegerType::class, [
            'label' => false,
            'required' => true,
            'attr' => [
                'min' => 1,
                'max' => 100,
                'class' => 'priority-input',
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PriorityChangeData::class, // You can bind this to a data object if needed
        ]);
    }
}