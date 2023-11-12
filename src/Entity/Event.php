<?php

namespace App\Entity;

use App\Content\Event\EventRepository;
use App\Content\Event\EventType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EventRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Event
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

    public function __construct()
    {
        $this->participants = new ArrayCollection();
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
}
