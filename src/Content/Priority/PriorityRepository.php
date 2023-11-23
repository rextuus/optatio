<?php

namespace App\Content\Priority;

use App\Entity\DesireList;
use App\Entity\Priority;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Priority>
 *
 * @method Priority|null find($id, $lockMode = null, $lockVersion = null)
 * @method Priority|null findOneBy(array $criteria, array $orderBy = null)
 * @method Priority[]    findAll()
 * @method Priority[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PriorityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Priority::class);
    }

    public function save(Priority $priority, bool $flush = true): void
    {
        $this->_em->persist($priority);
        if($flush){
            $this->_em->flush();
        }
    }
//    /**
//     * @return Priority[] Returns an array of Priority objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Priority
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
    public function getHighestPriorityByList(DesireList $desireList): float|bool|int|string|null
    {
        $qb = $this->createQueryBuilder('p');
        $qb->select('max(p.value) as max');
        $qb->where($qb->expr()->eq('p.desireList', ':desireList'));
        $qb->setParameter('desireList', $desireList->getId());

        return $qb->getQuery()->getSingleScalarResult();
    }
}
