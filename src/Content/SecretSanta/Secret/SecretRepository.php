<?php

namespace App\Content\SecretSanta\Secret;

use App\Entity\Event;
use App\Entity\Secret;
use App\Entity\SecretSantaEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use function Doctrine\ORM\QueryBuilder;

/**
 * @extends ServiceEntityRepository<Secret>
 *
 * @method Secret|null find($id, $lockMode = null, $lockVersion = null)
 * @method Secret|null findOneBy(array $criteria, array $orderBy = null)
 * @method Secret[]    findAll()
 * @method Secret[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SecretRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Secret::class);
    }

    public function save(Secret $secret, bool $flush = true): void
    {
        $this->_em->persist($secret);
        if($flush){
            $this->_em->flush();
        }
    }

//    /**
//     * @return Secret[] Returns an array of Secret objects
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

//    public function findOneBySomeField($value): ?Secret
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
    public function getStatistic(SecretSantaEvent $ssEvent)
    {
        $qb = $this->createQueryBuilder('s');
        $qb->join('s.event', 'e'); // Assuming 'event' is the property that refers to the Event entity in SecretSantaEvent

        $qb->select('COUNT(s.id) as amount, e.id as eventId, s.retrieved as retrievedState');
        $qb->where($qb->expr()->eq('s.secretSantaEvent', ':ssEvent'));
        $qb->setParameter('ssEvent', $ssEvent);
        $qb->groupBy('e.id, s.retrieved');
        return ($qb->getQuery()->getResult());
    }
}
