<?php

namespace App\Entity;

use App\Repository\SecretBackupRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SecretBackupRepository::class)]
class SecretBackup
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToMany(mappedBy: 'secretBackup', targetEntity: Secret::class, cascade: ['persist', 'remove'])]
    private Collection $secrets;

    #[ORM\Column]
    private ?int $round = null;

    #[ORM\ManyToOne(inversedBy: 'secretBackups')]
    #[ORM\JoinColumn(nullable: false)]
    private ?SecretSantaEvent $secretSantaEvent = null;

    public function __construct()
    {
        $this->secrets = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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
            $secret->setSecretBackup($this);
        }

        return $this;
    }

    public function removeSecret(Secret $secret): static
    {
        if ($this->secrets->removeElement($secret)) {
            // set the owning side to null (unless already changed)
            if ($secret->getSecretBackup() === $this) {
                $secret->setSecretBackup(null);
            }
        }

        return $this;
    }

    public function getRound(): ?int
    {
        return $this->round;
    }

    public function setRound(int $round): static
    {
        $this->round = $round;

        return $this;
    }

    public function getSecretSantaEvent(): ?SecretSantaEvent
    {
        return $this->secretSantaEvent;
    }

    public function setSecretSantaEvent(?SecretSantaEvent $secretSantaEvent): static
    {
        $this->secretSantaEvent = $secretSantaEvent;

        return $this;
    }
}
