<?php

namespace App\Entity;

use App\Content\SecretSanta\SecretSantaEvent\SecretSantaEventRepository;
use App\Content\SecretSanta\SecretSantaState;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SecretSantaEventRepository::class)]
class SecretSantaEvent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Event $firstRound = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Event $secondRound = null;

    #[ORM\Column(type: "string", enumType: SecretSantaState::class)]
    private SecretSantaState $state;

    #[ORM\ManyToOne(inversedBy: 'secretSantaEvents')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $creator = null;

    #[ORM\OneToMany(mappedBy: 'event', targetEntity: Exclusion::class)]
    private Collection $exclusions;

    #[ORM\OneToMany(mappedBy: 'secretSantaEvent', targetEntity: Secret::class)]
    private Collection $secrets;

    public function __construct()
    {
        $this->exclusions = new ArrayCollection();
        $this->secrets = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getFirstRound(): ?Event
    {
        return $this->firstRound;
    }

    public function setFirstRound(Event $firstRound): static
    {
        $this->firstRound = $firstRound;

        return $this;
    }

    public function getSecondRound(): ?Event
    {
        return $this->secondRound;
    }

    public function setSecondRound(?Event $secondRound): SecretSantaEvent
    {
        $this->secondRound = $secondRound;
        return $this;
    }

    public function getState(): ?SecretSantaState
    {
        return $this->state;
    }

    public function setState(SecretSantaState $state): static
    {
        $this->state = $state;

        return $this;
    }

    public function getCreator(): ?User
    {
        return $this->creator;
    }

    public function setCreator(?User $creator): static
    {
        $this->creator = $creator;

        return $this;
    }

    public function isOpenToJoin(): bool
    {
        return $this->state === SecretSantaState::OPEN;
    }

    /**
     * @return Collection<int, Exclusion>
     */
    public function getExclusions(): Collection
    {
        return $this->exclusions;
    }

    public function addExclusion(Exclusion $exclusion): static
    {
        if (!$this->exclusions->contains($exclusion)) {
            $this->exclusions->add($exclusion);
            $exclusion->setEvent($this);
        }

        return $this;
    }

    public function removeExclusion(Exclusion $exclusion): static
    {
        if ($this->exclusions->removeElement($exclusion)) {
            // set the owning side to null (unless already changed)
            if ($exclusion->getEvent() === $this) {
                $exclusion->setEvent(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Secret>
     */
    public function getSecrets(): Collection
    {
        return $this->secrets;
    }

    public function addSecret(Secret $secret): static
    {
        if (!$this->secrets->contains($secret)) {
            $this->secrets->add($secret);
            $secret->setSecretSantaEvent($this);
        }

        return $this;
    }

    public function removeSecret(Secret $secret): static
    {
        if ($this->secrets->removeElement($secret)) {
            // set the owning side to null (unless already changed)
            if ($secret->getSecretSantaEvent() === $this) {
                $secret->setSecretSantaEvent(null);
            }
        }

        return $this;
    }
}
