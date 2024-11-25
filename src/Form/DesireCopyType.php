<?php

namespace App\Form;

use App\Content\DesireList\Data\DesireCopyData;
use App\Content\DesireList\DesireListRepository;
use App\Entity\Desire;
use App\Entity\DesireList;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfonycasts\DynamicForms\DependentField;
use Symfonycasts\DynamicForms\DynamicFormBuilder;

class DesireCopyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder = new DynamicFormBuilder($builder);

        /** @var User $user */
        $user = $options['user'];

        /** @var DesireList $masterList */
        $masterList = $user->getDesireLists()->filter(function (DesireList $desireList) {
            return $desireList->isMaster();
        })->first();

        $builder->add('from', EntityType::class, [
            'class' => DesireList::class,
            'label' => 'Von',
            'empty_data' => $masterList,
            'query_builder' => function (DesireListRepository $repository) use ($user) {
                $qb = $repository->createQueryBuilder('d');
                $qb->select('DISTINCT d');
                $qb->where('d.owner = :owner');
                $qb->setParameter('owner', $user);

                return $qb;
            },
        ]);

        $builder->addDependent(
            'desires',
            ['from'],
            function (DependentField $field, ?DesireList $desireList) {
                $choices = [];

                if ($desireList) {
                    $desires = $desireList->getDesires()->toArray();
                    foreach ($desires as $desire) {
                        $choices[$desire->getName()] = $desire;
                    }
                }

                $field->add(ChoiceType::class, [
                    'label' => 'Wünsche',
                    'choices' => $choices,
                    'multiple' => true,
                    'expanded' => true,
                ]);
            }
        );

        $builder->addDependent(
            'to',
            ['from'],
            function (DependentField $field, ?DesireList $fromDesireList) use ($user, $builder) {
                $field->add(EntityType::class, [
                    'class' => DesireList::class,
                    'label' => 'Nach',
                    'query_builder' => function (DesireListRepository $repository) use ($user, $fromDesireList) {
                        $qb = $repository->createQueryBuilder('d');
                        $qb->select('DISTINCT d');
                        $qb->where('d.owner = :owner');
                        $qb->setParameter('owner', $user);

                        if ($fromDesireList) {
                            $qb->andWhere('d.id != :fromDesireListId');
                            $qb->setParameter('fromDesireListId', $fromDesireList->getId());
                        }

                        return $qb;
                    },
                ]);
            }
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DesireCopyData::class,
            'user' => null,
        ]);

        $resolver->setAllowedTypes('user', ['null', User::class]);
    }
}
