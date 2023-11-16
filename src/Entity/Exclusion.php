<?php

namespace App\Entity;

use App\Content\SecretSanta\Exclusion\ExclusionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ExclusionRepository::class)]
class Exclusion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'exclusions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?SecretSantaEvent $event = null;

    #[ORM\ManyToOne(inversedBy: 'exclusions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $exclusionCreator = null;

    #[ORM\ManyToOne(inversedBy: 'exclusions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $excludedUser = null;

    #[ORM\Column]
    private ?bool $bidirectional = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEvent(): ?SecretSantaEvent
    {
        return $this->event;
    }

    public function setEvent(?SecretSantaEvent $event): static
    {
        $this->event = $event;

        return $this;
    }

    public function getExclusionCreator(): ?User
    {
        return $this->exclusionCreator;
    }

    public function setExclusionCreator(?User $exclusionCreator): static
    {
        $this->exclusionCreator = $exclusionCreator;

        return $this;
    }

    public function getExcludedUser(): ?User
    {
        return $this->excludedUser;
    }

    public function setExcludedUser(?User $excludedUser): static
    {
        $this->excludedUser = $excludedUser;

        return $this;
    }

    public function getForbiddenUserId(mixed $randomProvider): ?int
    {
        if ($this->exclusionCreator->getId() === $randomProvider){
            return $this->excludedUser->getId();
        }

        if ($this->bidirectional && $this->excludedUser->getId() === $randomProvider){
            return $this->exclusionCreator->getId();
        }

        return null;
    }

    public function isBidirectional(): ?bool
    {
        return $this->bidirectional;
    }

    public function setBidirectional(bool $bidirectional): static
    {
        $this->bidirectional = $bidirectional;

        return $this;
    }
}
