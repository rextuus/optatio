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
        $masterList = $user->getDesireLists()->filter(fn(DesireList $list) => $list->isMaster())->first();

        $builder->add('action', ChoiceType::class, [
            'label' => 'Aktion',
            'choices' => ActionType::cases(),
            'choice_label' => fn(?ActionType $action) => $action?->getLabel(),
            'required' => true,
            'expanded' => false,
            'multiple' => false,
        ]);

        $builder->add('from', EntityType::class, [
            'class' => DesireList::class,
            'label' => 'Von',
            'empty_data' => $masterList,
            'query_builder' => fn(DesireListRepository $repository) => $repository->createQueryBuilder('d')
                ->select('DISTINCT d')
                ->where('d.owner = :owner')
                ->setParameter('owner', $user),
        ]);

        $builder->addDependent(
            'desires',
            ['from'],
            function (DependentField $field, ?DesireList $desireList) {
                $choices = $desireList ? array_combine(
                    $desireList->getDesires()->map(fn($desire) => $desire->getName())->toArray(),
                    $desireList->getDesires()->toArray()
                ) : [];

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
            function (DependentField $field, ?DesireList $fromDesireList) use ($user, $formData) {
                // Fetch available DesireLists
                $repository = $this->entityManager->getRepository(DesireList::class);
                $lists = $repository->getListForUserExcludingSelectedOne($user, $fromDesireList);

                // Set default value
                $defaultToList = $lists[0] ?? null;
                if (!$defaultToList) {
                    throw new \LogicException('No available DesireList for the user.');
                }
                $formData->setTo($defaultToList);

                $field->add(EntityType::class, [
                    'class' => DesireList::class,
                    'label' => 'Nach',
                    'query_builder' => function (DesireListRepository $repository) use ($user, $fromDesireList) {
                        $qb = $repository->createQueryBuilder('d')
                            ->select('DISTINCT d')
                            ->where('d.owner = :owner')
                            ->setParameter('owner', $user);

                        if ($fromDesireList) {
                            $qb->andWhere('d.id != :fromDesireListId')
                                ->setParameter('fromDesireListId', $fromDesireList->getId());
                        }

                        return $qb;
                    },
                    'required' => true,
                    'data' => $defaultToList,
                    'choice_value' => 'id',
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