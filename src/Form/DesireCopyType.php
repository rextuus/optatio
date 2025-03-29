<?php

namespace App\Form;

use App\Content\Desire\ActionType;
use App\Content\DesireList\Data\DesireCopyData;
use App\Content\DesireList\DesireListRepository;
use App\Entity\DesireList;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfonycasts\DynamicForms\DependentField;
use Symfonycasts\DynamicForms\DynamicFormBuilder;

class DesireCopyType extends AbstractType
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder = new DynamicFormBuilder($builder);

        /** @var User $user */
        $user = $options['user'];


        /** @var DesireCopyData $formData */
        $formData = $options['form_data'];

        /** @var DesireList $masterList */
        $masterList = $user->getDesireLists()->filter(function (DesireList $desireList) {
            return $desireList->isMaster();
        })->first();

        $builder->add('action', ChoiceType::class, [
            'label' => 'Aktion',
            'choices' => ActionType::cases(), // Use all enum values
            'choice_label' => function (?ActionType $action) {
                return $action?->getLabel(); // Use the label from the enum
            },
            'required' => true,
            'expanded' => false, // Dropdown menu
            'multiple' => false, // Single selection
        ]);

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
                    'choice_label' => 'name',
                    'choice_value' => 'id',
                ]);
            }
        );

        $builder->addDependent(
            'to',
            ['from'],
            function (DependentField $field, ?DesireList $fromDesireList) use ($user, $builder, $formData) {
                $queryBuilder = function (DesireListRepository $repository) use ($user, $fromDesireList) {
                    $qb = $repository->createQueryBuilder('d');
                    $qb->select('DISTINCT d');
                    $qb->where('d.owner = :owner');
                    $qb->setParameter('owner', $user);

                    if ($fromDesireList) {
                        $qb->andWhere('d.id != :fromDesireListId');
                        $qb->setParameter('fromDesireListId', $fromDesireList->getId());
                    }

                    return $qb;
                };

                // Fetch the first value from the query builder result
                $repository = $this->entityManager->getRepository(DesireList::class);

                $firstDesireList = $repository->getListForUserExcludingSelectedOne($user, $fromDesireList)[0];
                $formData->setTo($firstDesireList);

                // Set the first value as default if no value is provided
                $field->add(EntityType::class, [
                    'class' => DesireList::class,
                    'label' => 'Nach',
                    'query_builder' => $queryBuilder,
                    'required' => true,
                    'data' => $firstDesireList, // Automatically set the first DesireList
                ]);
            }
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => true,
            'data_class' => DesireCopyData::class,
            'user' => null,
            'form_data' => null,
        ]);

        $resolver->setAllowedTypes('user', ['null', User::class]);
        $resolver->setAllowedTypes('form_data', ['null', DesireCopyData::class]);
    }
}
