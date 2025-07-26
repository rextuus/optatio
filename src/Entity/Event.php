<?php

namespace App\Entity;

use App\Content\Event\EventInterface;
use App\Content\Event\EventRepository;
use App\Content\Event\EventType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EventRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Event implements EventInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'events')]
    private Collection $participants;

    #[ORM\ManyToOne(inversedBy: 'createdEvents')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $creator = null;

    #[ORM\Column(type: "string", enumType: EventType::class)]
    private EventType $eventType;

    #[ORM\Column]
    private ?bool $openToJoin = null;

    #[ORM\Column(type: Types::ARRAY)]
    private array $accessRoles = [];

    #[ORM\OneToMany(mappedBy: 'event', targetEntity: Secret::class)]
    private Collection $secrets;

    #[ORM\ManyToMany(targetEntity: DesireList::class, mappedBy: 'events')]
    private Collection $desireLists;

    /**
     * @var Collection<int, EventBookmark>
     */
    #[ORM\OneToMany(mappedBy: 'event', targetEntity: EventBookmark::class)]
    private Collection $eventBookmarks;

    public function __construct()
    {
        $this->participants = new ArrayCollection();
        $this->secrets = new ArrayCollection();
        $this->desireLists = new ArrayCollection();
        $this->eventBookmarks = new ArrayCollection();
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

    /**
     * @return Collection<int, User>
     */
    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function addParticipant(User $participant): static
    {
        if (!$this->participants->contains($participant)) {
            $this->participants->add($participant);
        }

        return $this;
    }

    public function removeParticipant(User $participant): static
    {
        $this->participants->removeElement($participant);

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

    public function getEventType(): EventType
    {
        return $this->eventType;
    }

    public function setEventType(EventType $eventType): Event
    {
        $this->eventType = $eventType;
        return $this;
    }

    public function isOpenToJoin(): ?bool
    {
        return $this->openToJoin;
    }

    public function setOpenToJoin(bool $openToJoin): static
    {
        $this->openToJoin = $openToJoin;

        return $this;
    }

    public function getAccessRoles(): array
    {
        return $this->accessRoles;
    }

    public function setAccessRoles(array $accessRoles): static
    {
        $this->accessRoles = $accessRoles;

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
            $secret->setEvent($this);
        }

        return $this;
    }

    public function removeSecret(Secret $secret): static
    {
        if ($this->secrets->removeElement($secret)) {
            // set the owning side to null (unless already changed)
            if ($secret->getEvent() === $this) {
                $secret->setEvent(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, DesireList>
     */
    public function getDesireLists(): Collection
    {
        return $this->desireLists;
    }

    public function addDesireList(DesireList $desireList): static
    {
        if (!$this->desireLists->contains($desireList)) {
            $this->desireLists->add($desireList);
            $desireList->addEvent($this);
        }

        return $this;
    }

    public function removeDesireList(DesireList $desireList): static
    {
        if ($this->desireLists->removeElement($desireList)) {
            $desireList->removeEvent($this);
        }

        return $this;
    }

    /**
     * @return array<User>
     */
    public function getParticipantsWithoutGodFathers(SecretSantaEvent $secretSantaEvent): array
    {
        $godFathers = $secretSantaEvent->getGodFathers()->toArray();
        $participants = $this->getParticipants()->toArray();

        // Get the IDs of the godFathers
        $godFatherIds = array_map(function($godFather) {
            return $godFather->getId();
        }, $godFathers);

        // Filter out the godFathers from the participants based on their IDs
        return array_filter($participants, function($participant) use ($godFatherIds) {
            return !in_array($participant->getId(), $godFatherIds);
        });
    }

    public function __toString(): string
    {
        return $this->name ?? '';
    }

    /**
     * @return Collection<int, EventBookmark>
     */
    public function getEventBookmarks(): Collection
    {
        return $this->eventBookmarks;
    }

    public function addEventBookmark(EventBookmark $eventBookmark): static
    {
        if (!$this->eventBookmarks->contains($eventBookmark)) {
            $this->eventBookmarks->add($eventBookmark);
            $eventBookmark->setEvent($this);
        }

        return $this;
    }

    public function removeEventBookmark(EventBookmark $eventBookmark): static
    {
        if ($this->eventBookmarks->removeElement($eventBookmark)) {
            // set the owning side to null (unless already changed)
            if ($eventBookmark->getEvent() === $this) {
                $eventBookmark->setEvent(null);
            }
        }

        return $this;
    }
}
