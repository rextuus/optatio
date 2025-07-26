<?php

namespace App\Content\Desire;

use App\Entity\Desire;
use App\Entity\DesireList;
use App\Entity\Priority;
use App\Entity\SecretSantaEvent;
use App\Entity\User;
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

    /**
     * @return array<Desire>
     */
    public function findDesiresWithUrlsAndNoExtractedImages(): array
    {
        $qb = $this->createQueryBuilder('d');

        $qb->join('d.urls', 'u')
            ->leftJoin('d.extractedDesireImageCollections', 'e')
            ->where($qb->expr()->isNotNull('u.id'))
            ->andWhere($qb->expr()->isNull('e.id'))
            ->distinct() // Prevent duplicates
            ->select('d')
            ->setMaxResults(20);

        return $qb->getQuery()->getResult();
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
    public function findByListOrderByPriority(DesireList $list, bool $isForeign = false)
    {
        $qb = $this->createQueryBuilder('d');
        $qb->join('d.desireLists', 'dl');
        $qb->leftJoin(Priority::class, 'p', 'WITH', 'p.desire = d.id');
        $qb->where('dl.id = :desireListId')
            ->setParameter('desireListId', $list->getId());
        $qb->andWhere($qb->expr()->eq('p.desireList', ':desireListId'));
        if ($isForeign){
            $qb->andWhere($qb->expr()->eq('d.listed', ':isForeign'));
            $qb->setParameter('isForeign', true);
        }


        $qb->orderBy('p.value', 'ASC');

        return $qb->getQuery()->getResult();
    }

    // TODO make this nicer software engineer guy
    public function getAllDesiresForSecretSantaEvent(SecretSantaEvent $event, $firstRound = true)
    {
        $round = $event->getFirstRound()->getId();
        if (!$firstRound){
            $round = $event->getSecondRound()->getId();
        }

        $qb = $this->createQueryBuilder('d');
        $qb->join(User::class, 'u', 'WITH', 'd.owner = u.id');
        $qb->join('d.desireLists', 'dl');
        $qb->join('dl.events', 'e');
        $qb->leftJoin('d.reservations', 'r');

        $qb->select('COUNT(d.id) as desires, u.id as user, e.id as round, r.id as reserved');

        $qb->where($qb->expr()->eq('e.id',':desireListId'))
            ->setParameter('desireListId', $round);

        $qb->groupBy('e.id, u.id, r.id');
        return ($qb->getQuery()->getResult());
    }
}
