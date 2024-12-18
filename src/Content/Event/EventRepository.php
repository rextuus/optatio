<?php

namespace App\Content\Event;

use App\Entity\Event;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Event>
 *
 * @method Event|null find($id, $lockMode = null, $lockVersion = null)
 * @method Event|null findOneBy(array $criteria, array $orderBy = null)
 * @method Event[]    findAll()
 * @method Event[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    public function save(Event $event, bool $flush = true): void
    {
        $this->_em->persist($event);
        if($flush){
            $this->_em->flush();
        }
    }
//    /**
//     * @return Event[] Returns an array of Event objects
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

//    public function findOneBySomeField($value): ?Event
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
    public function findEventsForUser(User $user)
    {
        $qb = $this->createQueryBuilder('e');

        return $qb->getQuery()->getResult();
    }

    public function findEventsWithoutSecretSantaRounds(?User $user = null)
    {
        $qb = $this->createQueryBuilder('e');
        $qb->where($qb->expr()->neq('e.eventType', ':type'));
        $qb->setParameter('type', EventType::SECRET_SANTA);

        if ($user !== null) {
            $qb->innerJoin('e.participants', 'p')
                ->andWhere('p = :user')
                ->setParameter('user', $user);
        }

        return $qb->getQuery()->getResult();
    }
}
