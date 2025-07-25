<?php

declare(strict_types=1);

namespace App\Entity;

use App\Content\DesireList\Relation\DesireListRelationType;
use App\Content\DesireList\Relation\DesireListRelationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DesireListRelationRepository::class)]
class DesireListRelation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: DesireList::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?DesireList $sourceList = null;

    #[ORM\ManyToOne(targetEntity: DesireList::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?DesireList $targetList = null;

    #[ORM\ManyToOne(targetEntity: Desire::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Desire $desire = null;

    #[ORM\Column(type: 'string', enumType: DesireListRelationType::class)]
    private ?DesireListRelationType $relationType = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSourceList(): ?DesireList
    {
        return $this->sourceList;
    }

    public function setSourceList(?DesireList $sourceList): static
    {
        $this->sourceList = $sourceList;

        return $this;
    }

    public function getTargetList(): ?DesireList
    {
        return $this->targetList;
    }

    public function setTargetList(?DesireList $targetList): static
    {
        $this->targetList = $targetList;

        return $this;
    }

    public function getDesire(): ?Desire
    {
        return $this->desire;
    }

    public function setDesire(?Desire $desire): static
    {
        $this->desire = $desire;

        return $this;
    }

    public function getRelationType(): ?DesireListRelationType
    {
        return $this->relationType;
    }

    public function setRelationType(DesireListRelationType $relationType): static
    {
        $this->relationType = $relationType;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}