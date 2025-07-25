<?php
declare(strict_types=1);

namespace App\Content\DesireList\Relation;

use App\Content\DesireList\Relation\Data\DesireListRelationData;
use App\Entity\Desire;
use App\Entity\DesireList;
use App\Entity\DesireListRelation;

/**
 * Service for managing DesireListRelation entities.
 */
class DesireListRelationService
{
    public function __construct(
        private readonly DesireListRelationRepository $repository,
        private readonly DesireListRelationFactory $factory,
    ) {
    }

    public function createByData(DesireListRelationData $data): DesireListRelation
    {
        $relation = $this->factory->createByData($data);
        $this->repository->save($relation);
        return $relation;
    }

    public function update(DesireListRelation $relation, DesireListRelationData $data): DesireListRelation
    {
        $relation = $this->factory->mapData($data, $relation);
        $this->repository->save($relation);
        return $relation;
    }

    /**
     * @return DesireListRelation[]
     */
    public function findBy(array $conditions): array
    {
        return $this->repository->findBy($conditions);
    }

    /**
     * @return DesireListRelation[]
     */
    public function findBySourceList(DesireList $sourceList): array
    {
        return $this->repository->findBySourceList($sourceList);
    }

    /**
     * @return DesireListRelation[]
     */
    public function findByTargetList(DesireList $targetList): array
    {
        return $this->repository->findByTargetList($targetList);
    }

    /**
     * @return DesireListRelation[]
     */
    public function findByDesire(Desire $desire): array
    {
        return $this->repository->findByDesire($desire);
    }

    /**
     * @return DesireListRelation[]
     */
    public function findByRelationType(DesireListRelationType $relationType): array
    {
        return $this->repository->findByRelationType($relationType);
    }

    /**
     * Creates a relation record for a shared desire between lists.
     */
    public function recordSharedDesire(DesireList $sourceList, DesireList $targetList, Desire $desire): DesireListRelation
    {
        $data = new DesireListRelationData();
        $data->setSourceList($sourceList);
        $data->setTargetList($targetList);
        $data->setDesire($desire);
        $data->setRelationType(DesireListRelationType::SHARED);
        
        return $this->createByData($data);
    }

    /**
     * Creates a relation record for a copied desire between lists.
     */
    public function recordCopiedDesire(DesireList $sourceList, DesireList $targetList, Desire $desire): DesireListRelation
    {
        $data = new DesireListRelationData();
        $data->setSourceList($sourceList);
        $data->setTargetList($targetList);
        $data->setDesire($desire);
        $data->setRelationType(DesireListRelationType::COPIED);
        
        return $this->createByData($data);
    }

    /**
     * Creates a relation record for a moved desire between lists.
     */
    public function recordMovedDesire(DesireList $sourceList, DesireList $targetList, Desire $desire): DesireListRelation
    {
        $data = new DesireListRelationData();
        $data->setSourceList($sourceList);
        $data->setTargetList($targetList);
        $data->setDesire($desire);
        $data->setRelationType(DesireListRelationType::MOVED);
        
        return $this->createByData($data);
    }
}