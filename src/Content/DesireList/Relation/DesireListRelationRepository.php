<?php

namespace App\Content\DesireList\Relation;

use App\Entity\Desire;
use App\Entity\DesireList;
use App\Entity\DesireListRelation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DesireListRelation>
 *
 * @method DesireListRelation|null find($id, $lockMode = null, $lockVersion = null)
 * @method DesireListRelation|null findOneBy(array $criteria, array $orderBy = null)
 * @method DesireListRelation[]    findAll()
 * @method DesireListRelation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DesireListRelationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DesireListRelation::class);
    }

    public function save(DesireListRelation $relation, bool $flush = true): void
    {
        $this->_em->persist($relation);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @return DesireListRelation[]
     */
    public function findBySourceList(DesireList $sourceList): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.sourceList = :sourceList')
            ->setParameter('sourceList', $sourceList)
            ->orderBy('r.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return DesireListRelation[]
     */
    public function findByTargetList(DesireList $targetList): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.targetList = :targetList')
            ->setParameter('targetList', $targetList)
            ->orderBy('r.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return DesireListRelation[]
     */
    public function findByDesire(Desire $desire): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.desire = :desire')
            ->setParameter('desire', $desire)
            ->orderBy('r.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return DesireListRelation[]
     */
    public function findByRelationType(DesireListRelationType $relationType): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.relationType = :relationType')
            ->setParameter('relationType', $relationType)
            ->orderBy('r.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array<DesireListRelation>
     */
    public function getRelationsForDesireAndTargetList(Desire $desire, DesireList $targetList): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.desire = :desire')
            ->andWhere('r.targetList = :targetList')
            ->setParameter('desire', $desire)
            ->setParameter('targetList', $targetList)
            ->orderBy('r.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
