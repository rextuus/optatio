<?php

namespace App\Entity;

use App\Content\SecretSanta\Secret\SecretRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SecretRepository::class)]
class Secret
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'secrets')]
    #[ORM\JoinColumn(nullable: false)]
    private ?SecretSantaEvent $secretSantaEvent = null;

    #[ORM\ManyToOne(inversedBy: 'secrets')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Event $event = null;

    #[ORM\ManyToOne(inversedBy: 'providingSecrets')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $provider = null;

    #[ORM\ManyToOne(inversedBy: 'receivingSecrets')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $receiver = null;

    #[ORM\Column]
    private ?bool $retrieved = null;

    #[ORM\ManyToOne(inversedBy: 'secrets')]
    private ?SecretBackup $secretBackup = null;

    public function getSecretSantaEvent(): ?SecretSantaEvent
    {
        return $this->secretSantaEvent;
    }

    public function setSecretSantaEvent(?SecretSantaEvent $secretSantaEvent): static
    {
        $this->secretSantaEvent = $secretSantaEvent;

        return $this;
    }

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function setEvent(?Event $event): static
    {
        $this->event = $event;

        return $this;
    }

    public function getProvider(): ?User
    {
        return $this->provider;
    }

    public function setProvider(?User $provider): static
    {
        $this->provider = $provider;

        return $this;
    }

    public function getReceiver(): ?User
    {
        return $this->receiver;
    }

    public function setReceiver(?User $receiver): static
    {
        $this->receiver = $receiver;

        return $this;
    }

    public function isRetrieved(): ?bool
    {
        return $this->retrieved;
    }

    public function setRetrieved(bool $retrieved): static
    {
        $this->retrieved = $retrieved;

        return $this;
    }

    public function getSecretBackup(): ?SecretBackup
    {
        return $this->secretBackup;
    }

    public function setSecretBackup(?SecretBackup $secretBackup): static
    {
        $this->secretBackup = $secretBackup;

        return $this;
    }

}
