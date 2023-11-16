<?php

namespace App\Content\DesireList;

use App\Entity\DesireList;
use App\Entity\Event;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use function Doctrine\ORM\QueryBuilder;

/**
 * @extends ServiceEntityRepository<DesireList>
 *
 * @method DesireList|null find($id, $lockMode = null, $lockVersion = null)
 * @method DesireList|null findOneBy(array $criteria, array $orderBy = null)
 * @method DesireList[]    findAll()
 * @method DesireList[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DesireListRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DesireList::class);
    }

    public function save(DesireList $desireList, bool $flush = true): void
    {
        $this->_em->persist($desireList);
        if($flush){
            $this->_em->flush();
        }
    }
//    /**
//     * @return DesireList[] Returns an array of DesireList objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('d.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?DesireList
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

    /**
     * @return DesireList[]
     */
    public function findByUserAndEvent(User $user, Event $event): array
    {
        $qb = $this->createQueryBuilder('d');
        $qb->join('d.events', 'e');
        $qb->where('e.id = :eventId')
            ->setParameter('eventId', $event->getId());
        $qb->andWhere($qb->expr()->eq('d.owner', ':owner'))
            ->setParameter('owner', $user);

        return $qb->getQuery()->getResult();
    }
}
