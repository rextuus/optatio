<?php

namespace App\Repository;

use App\Entity\EventSetting;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EventSetting>
 *
 * @method EventSetting|null find($id, $lockMode = null, $lockVersion = null)
 * @method EventSetting|null findOneBy(array $criteria, array $orderBy = null)
 * @method EventSetting[]    findAll()
 * @method EventSetting[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventSettingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EventSetting::class);
    }

//    /**
//     * @return EventSetting[] Returns an array of EventSetting objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('e.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?EventSetting
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
