<?php
declare(strict_types=1);

namespace App\Form;

use App\Content\SecretSanta\SecretSantaEvent\Data\SecretSantaEventJoinData;
use App\Content\SecretSanta\SecretSantaEvent\Data\SecretSantaStartData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2023 DocCheck Community GmbH
 */
class SecretSantaStartType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('checkSum', PasswordType::class, ['required' => true])
            ->add('start', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SecretSantaStartData::class
        ]);
    }
}
