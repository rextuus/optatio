<?php

namespace App\Content\SecretSanta\SecretSantaEvent;

use App\Entity\Event;
use App\Entity\SecretSantaEvent;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SecretSantaEvent>
 *
 * @method SecretSantaEvent|null find($id, $lockMode = null, $lockVersion = null)
 * @method SecretSantaEvent|null findOneBy(array $criteria, array $orderBy = null)
 * @method SecretSantaEvent[]    findAll()
 * @method SecretSantaEvent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SecretSantaEventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SecretSantaEvent::class);
    }

    public function save(SecretSantaEvent $secretSantaEvent, bool $flush = true): void
    {
        $this->_em->persist($secretSantaEvent);
        if($flush){
            $this->_em->flush();
        }
    }

//    /**
//     * @return SecretSantaEvent[] Returns an array of SecretSantaEvent objects
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

//    public function findOneBySomeField($value): ?SecretSantaEvent
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
    public function findByFirstOrSecondRound(Event $event): array
    {
        $qb = $this->createQueryBuilder('s');
        $qb->where($qb->expr()->eq('s.firstRound', ':event'));
        $qb->orWhere($qb->expr()->eq('s.secondRound', ':event'));
        $qb->setParameter(':event', $event);

        return $qb->getQuery()->getResult();
    }

    /**
     * @return array<SecretSantaEvent>
     */
    public function findSecretSantaEvents(?User $user = null): array
    {
        $qb = $this->createQueryBuilder('sse');

        if ($user !== null) {
            $qb->leftJoin('sse.firstRound', 'fr')
                ->leftJoin('sse.secondRound', 'sr')
                ->leftJoin('fr.participants', 'frp')
                ->leftJoin('sr.participants', 'srp')
                ->where('frp = :user OR srp = :user OR sse.creator = :user')
                ->setParameter('user', $user);
        }

        $query = $qb->getQuery();
        return $query->getResult();
    }
}
