<?php

namespace App\Form;

use App\Content\Desire\Data\DesireData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DesireCreateType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class)
            ->add('description', TextareaType::class)
            ->add('url1', UrlType::class,['required' => false])
            ->add('url2', UrlType::class,['required' => false])
            ->add('url3', UrlType::class,['required' => false])
            ->add('exactly', CheckboxType::class, ['required' => false])
            ->add('exclusive', CheckboxType::class, ['required' => false, 'attr' => ['checked' => 'checked']])
            ->add('submit', SubmitType::class, ['label' => 'Erstellen'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'class_data' => DesireData::class
        ]);
    }
}
