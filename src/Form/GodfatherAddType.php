<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\SecretSantaEvent;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GodfatherAddType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var SecretSantaEvent $secretSantaEvent */
        $secretSantaEvent = $options['secret_santa_event'];

        // Get overall participants
        $participants = $secretSantaEvent->getOverallParticipants();
        $godfathers = $secretSantaEvent->getGodfathers()->toArray();
        $candidates = array_udiff($participants, $godfathers, function($a, $b) {
            return $a->getId() - $b->getId();
        });

        // Extract user choices from participants
        $userChoices = [];
        foreach ($candidates as $participant) {
            $userChoices[$participant->getFullName()] = $participant;
        }

        $builder
            ->add('user', ChoiceType::class, [
                'choices' => $userChoices,
                'choice_label' => function(User $user) {
                    return $user->getFullName();
                },
                'placeholder' => 'Select a user',
            ])
            ->add('save', SubmitType::class, ['label' => 'Hinzufügen'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Uncomment and adjust according to your data class
            // 'data_class' => YourDataClass::class,
            'secret_santa_event' => null,
        ]);
        $resolver->setAllowedTypes('secret_santa_event', SecretSantaEvent::class);
    }
}