<?php
declare(strict_types=1);

namespace App\Content\DesireList\Relation\Data;

use App\Content\DesireList\Relation\DesireListRelationType;
use App\Entity\Desire;
use App\Entity\DesireList;
use App\Entity\DesireListRelation;

/**
 * Data transfer object for DesireListRelation entity.
 */
class DesireListRelationData
{
    private ?DesireList $sourceList = null;
    private ?DesireList $targetList = null;
    private ?Desire $desire = null;
    private ?DesireListRelationType $relationType = null;
    private ?\DateTimeImmutable $createdAt = null;

    public function getSourceList(): ?DesireList
    {
        return $this->sourceList;
    }

    public function setSourceList(?DesireList $sourceList): self
    {
        $this->sourceList = $sourceList;
        return $this;
    }

    public function getTargetList(): ?DesireList
    {
        return $this->targetList;
    }

    public function setTargetList(?DesireList $targetList): self
    {
        $this->targetList = $targetList;
        return $this;
    }

    public function getDesire(): ?Desire
    {
        return $this->desire;
    }

    public function setDesire(?Desire $desire): self
    {
        $this->desire = $desire;
        return $this;
    }

    public function getRelationType(): ?DesireListRelationType
    {
        return $this->relationType;
    }

    public function setRelationType(?DesireListRelationType $relationType): self
    {
        $this->relationType = $relationType;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function initFromEntity(DesireListRelation $relation): self
    {
        $this->setSourceList($relation->getSourceList());
        $this->setTargetList($relation->getTargetList());
        $this->setDesire($relation->getDesire());
        $this->setRelationType($relation->getRelationType());
        $this->setCreatedAt($relation->getCreatedAt());

        return $this;
    }
}