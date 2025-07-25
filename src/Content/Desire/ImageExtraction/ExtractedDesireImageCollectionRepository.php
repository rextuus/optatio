<?php

namespace App\Content\Desire\ImageExtraction;

use App\Entity\ExtractedDesireImageCollection;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ExtractedDesireImageCollection>
 *
 * @method ExtractedDesireImageCollection|null find($id, $lockMode = null, $lockVersion = null)
 * @method ExtractedDesireImageCollection|null findOneBy(array $criteria, array $orderBy = null)
 * @method ExtractedDesireImageCollection[]    findAll()
 * @method ExtractedDesireImageCollection[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExtractedDesireImageCollectionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExtractedDesireImageCollection::class);
    }

    public function save(ExtractedDesireImageCollection $collection, bool $flush = true): void
    {
        $this->_em->persist($collection);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function findPendingExtractions(): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.status = :status')
            ->setParameter('status', 'pending')
            ->getQuery()
            ->getResult();
    }

    public function findByExtractionId(string $extractionId): ?ExtractedDesireImageCollection
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.extractionId = :extractionId')
            ->setParameter('extractionId', $extractionId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByDesire(int $desireId): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.desire = :desireId')
            ->setParameter('desireId', $desireId)
            ->orderBy('e.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
