<?php

namespace App\Content\User;

use App\Entity\UserAccessRoles;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserAccessRoles>
 *
 * @method UserAccessRoles|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserAccessRoles|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserAccessRoles[]    findAll()
 * @method UserAccessRoles[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserAccessRolesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserAccessRoles::class);
    }

    public function save(UserAccessRoles $userAccessRoles, bool $flush = true): void
    {
        $this->_em->persist($userAccessRoles);
        if($flush){
            $this->_em->flush();
        }
    }
//    /**
//     * @return UserAccessRoles[] Returns an array of UserAccessRoles objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('u.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?UserAccessRoles
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
