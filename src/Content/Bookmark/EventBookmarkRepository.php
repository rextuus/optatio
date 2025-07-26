<?php

namespace App\Content\Bookmark;

use App\Entity\EventBookmark;
use App\Entity\SecretSantaEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EventBookmark>
 */
class EventBookmarkRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EventBookmark::class);
    }

    public function save(EventBookmark $eventBookmark, bool $flush = true): void
    {
        $this->_em->persist($eventBookmark);
        if($flush){
            $this->_em->flush();
        }
    }

    public function delete(EventBookmark $eventBookmark, bool $flush = true): void
    {
        $this->_em->remove($eventBookmark);
        if($flush){
            $this->_em->flush();
        }
    }

    //    /**
    //     * @return EventBookmark[] Returns an array of EventBookmark objects
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

    //    public function findOneBySomeField($value): ?EventBookmark
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
