<?php

namespace App\Content\User;

use App\Entity\AccessRole;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AccessRole>
 *
 * @method AccessRole|null find($id, $lockMode = null, $lockVersion = null)
 * @method AccessRole|null findOneBy(array $criteria, array $orderBy = null)
 * @method AccessRole[]    findAll()
 * @method AccessRole[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AccessRoleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AccessRole::class);
    }
    
    public function save(AccessRole $accessRole, bool $flush = true): void
    {
        $this->_em->persist($accessRole);
        if($flush){
            $this->_em->flush();
        }
    }

//    /**
//     * @return AccessRole[] Returns an array of AccessRole objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?AccessRole
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
