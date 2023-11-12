<?php

namespace App\Content\Desire;

use App\Entity\Desire;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Desire>
 *
 * @method Desire|null find($id, $lockMode = null, $lockVersion = null)
 * @method Desire|null findOneBy(array $criteria, array $orderBy = null)
 * @method Desire[]    findAll()
 * @method Desire[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DesireRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Desire::class);
    }

    public function save(Desire $desire, bool $flush = true): void
    {
        $this->_em->persist($desire);
        if($flush){
            $this->_em->flush();
        }
    }

//    /**
//     * @return Desire[] Returns an array of Desire objects
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

//    public function findOneBySomeField($value): ?Desire
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
