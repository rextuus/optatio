<?php

namespace App\Entity;

use App\Content\Bookmark\EventBookmarkRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EventBookmarkRepository::class)]
class EventBookmark
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'eventBookmarks')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    #[ORM\ManyToOne(inversedBy: 'eventBookmarks')]
    private ?Event $event = null;

    #[ORM\ManyToOne(inversedBy: 'eventBookmarks')]
    private ?SecretSantaEvent $secretSantaEvent = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;

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
