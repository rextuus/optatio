<?php
declare(strict_types=1);

namespace App\Content\DesireList\Relation;

use App\Content\DesireList\Relation\Data\DesireListRelationData;
use App\Entity\DesireListRelation;

/**
 * Factory for creating and updating DesireListRelation entities.
 */
class DesireListRelationFactory
{
    public function createByData(DesireListRelationData $data): DesireListRelation
    {
        $relation = $this->createNewInstance();
        $this->mapData($data, $relation);
        return $relation;
    }

    public function mapData(DesireListRelationData $data, DesireListRelation $relation): DesireListRelation
    {
        $relation->setSourceList($data->getSourceList());
        $relation->setTargetList($data->getTargetList());
        $relation->setDesire($data->getDesire());
        $relation->setRelationType($data->getRelationType());
        
        if ($data->getCreatedAt() !== null) {
            $relation->setCreatedAt($data->getCreatedAt());
        }

        return $relation;
    }

    private function createNewInstance(): DesireListRelation
    {
        return new DesireListRelation();
    }
}