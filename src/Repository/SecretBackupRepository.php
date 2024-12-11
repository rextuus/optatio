<?php

namespace App\Repository;

use App\Entity\Secret;
use App\Entity\SecretBackup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SecretBackup>
 *
 * @method SecretBackup|null find($id, $lockMode = null, $lockVersion = null)
 * @method SecretBackup|null findOneBy(array $criteria, array $orderBy = null)
 * @method SecretBackup[]    findAll()
 * @method SecretBackup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SecretBackupRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SecretBackup::class);
    }

    public function save(SecretBackup $secret, bool $flush = true): void
    {
        $this->_em->persist($secret);
        if($flush){
            $this->_em->flush();
        }
    }

//    /**
//     * @return SecretBackup[] Returns an array of SecretBackup objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?SecretBackup
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
