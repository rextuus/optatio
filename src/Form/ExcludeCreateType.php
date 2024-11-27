<?php

namespace App\Form;

use App\Content\SecretSanta\Exclusion\Data\ExclusionData;
use App\Content\SecretSanta\Exclusion\ExclusionRepository;
use App\Content\User\UserRepository;
use App\Entity\Exclusion;
use App\Entity\SecretSantaEvent;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Orm\EntityRepositoryInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExcludeCreateType extends AbstractType
{
    public function __construct(private ExclusionRepository $exclusionRepository)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $exclusionCreator = $options['currentUser']; // The user who is excluding
        /** @var SecretSantaEvent $event */
        $event = $options['event']; // The user who is excluding

        $existingExclusions = $this->exclusionRepository->findBy(['exclusionCreator' => $exclusionCreator, 'event' => $event]);

        $ids = array_map(
            function (Exclusion $exclusion) {
                return $exclusion->getId();
            },
            $existingExclusions
        );

        $builder
            ->add('excludedUser', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'firstName', // Replace with the field you want to display
                'query_builder' => function (UserRepository $repository) use ($exclusionCreator, $event, $ids) {
                    $qb = $repository->createQueryBuilder('u');

                    $qb->join('u.events', 'e');
                        $qb->leftJoin(Exclusion::class, 'x', Join::WITH, 'u.id = x.excludedUser and x.event = e');
                        $qb->where('e = :firstRoundEvent OR e = :secondRoundEvent');
                        $qb->andWhere('u != :currentUser');
                        $qb->andWhere(
                            $qb->expr()->orX(
                                $qb->expr()->notIn('x.id', ':ids'),
                                $qb->expr()->isNull('x.id')
                            )
                        );
                        $qb->setParameter('firstRoundEvent', $event->getFirstRound());
                        $qb->setParameter('secondRoundEvent', $event->getSecondRound());
                        $qb->setParameter('currentUser', $exclusionCreator);
                        $qb->setParameter('ids', $ids);
                    return $qb;
                },
//                'mapped' => false, // This field is not mapped to an entity property
                'label' => 'Select participant to exclude',
            ])
            ->add('submit', SubmitType::class, ['label' => 'Ausschließen']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ExclusionData::class,
            'currentUser' => null,
            'event' => null,
        ]);
        $resolver->setAllowedTypes('event', SecretSantaEvent::class);
        $resolver->setAllowedTypes('currentUser', User::class);

    }
}
