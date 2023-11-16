<?php

namespace App\Content\SecretSanta\Exclusion;

use App\Entity\Exclusion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Exclusion>
 *
 * @method Exclusion|null find($id, $lockMode = null, $lockVersion = null)
 * @method Exclusion|null findOneBy(array $criteria, array $orderBy = null)
 * @method Exclusion[]    findAll()
 * @method Exclusion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExclusionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Exclusion::class);
    }

    public function save(Exclusion $exclusion, bool $flush = true): void
    {
        $this->_em->persist($exclusion);
        if($flush){
            $this->_em->flush();
        }
    }
//    /**
//     * @return Exclusion[] Returns an array of Exclusion objects
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

//    public function findOneBySomeField($value): ?Exclusion
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
