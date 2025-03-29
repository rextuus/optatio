<?php

namespace App\Entity;

use App\Content\DesireList\DesireListRepository;
use App\Content\User\HasAccessRoleInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DesireListRepository::class)]
class DesireList implements HasAccessRoleInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'desireLists')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    #[ORM\ManyToMany(targetEntity: Desire::class, inversedBy: 'desireLists')]
    private Collection $desires;

    #[ORM\ManyToMany(targetEntity: Event::class, inversedBy: 'desireLists')]
    private Collection $events;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\OneToMany(mappedBy: 'desireList', targetEntity: Priority::class)]
    private Collection $priorities;

    #[ORM\ManyToMany(targetEntity: AccessRole::class, mappedBy: 'desireLists')]
    private Collection $accessRoles;

    #[ORM\Column(type: 'boolean')]
    private ?bool $master = false;

    #[ORM\Column (
        type: Types::BOOLEAN,
        options: [
            'default' => false,
        ])]
    private bool $hasPriority = false;

    public function __construct()
    {
        $this->desires = new ArrayCollection();
        $this->events = new ArrayCollection();
        $this->priorities = new ArrayCollection();
        $this->accessRoles = new ArrayCollection();
    }

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

    /**
     * @return Collection<int, Desire>
     */
    public function getDesires(): Collection
    {
        return $this->desires;
    }

    public function addDesire(Desire $desire): static
    {
        if (!$this->desires->contains($desire)) {
            $this->desires->add($desire);
        }

        return $this;
    }

    public function removeDesire(Desire $desire): static
    {
        $this->desires->removeElement($desire);

        return $this;
    }


    /**
     * @return Collection<int, Event>
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function addEvent(Event $event): static
    {
        if (!$this->events->contains($event)) {
            $this->events->add($event);
        }

        return $this;
    }

    public function removeEvent(Event $event): static
    {
        $this->events->removeElement($event);

        return $this;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection<int, Priority>
     */
    public function getPriorities(): Collection
    {
        return $this->priorities;
    }

    public function addPriority(Priority $priority): static
    {
        if (!$this->priorities->contains($priority)) {
            $this->priorities->add($priority);
            $priority->setDesireList($this);
        }

        return $this;
    }

    public function removePriority(Priority $priority): static
    {
        if ($this->priorities->removeElement($priority)) {
            // set the owning side to null (unless already changed)
            if ($priority->getDesireList() === $this) {
                $priority->setDesireList(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, AccessRole>
     */
    public function getAccessRoles(): Collection
    {
        return $this->accessRoles;
    }

    public function addAccessRole(AccessRole $accessRole): static
    {
        if (!$this->accessRoles->contains($accessRole)) {
            $this->accessRoles->add($accessRole);
            $accessRole->addDesireList($this);
        }

        return $this;
    }

    public function removeAccessRole(AccessRole $accessRole): static
    {
        if ($this->accessRoles->removeElement($accessRole)) {
            $accessRole->removeDesireList($this);

        }

        return $this;
    }

    public function isMaster(): bool
    {
        if ($this->master === null) {
            $this->master = false;
        }

        return $this->master;
    }

    public function setMaster(bool $master): static
    {
        $this->master = $master;

        return $this;
    }

    public function __toString(): string
    {

        $heart = $this->isMaster() ? ' ✨' : ' ❤️';

        return $heart . ' ' . $this->getName() . ' (' . $this->getDesires()->count() . ')';
    }

    public function hasPriority(): ?bool
    {
        return $this->hasPriority;
    }

    public function setHasPriority(bool $hasPriority): static
    {
        $this->hasPriority = $hasPriority;

        return $this;
    }
}
