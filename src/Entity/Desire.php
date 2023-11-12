<?php

namespace App\Entity;

use App\Content\Desire\DesireRepository;
use App\Content\Desire\DesireState;
use App\Service\Evaluation\BetOn;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DesireRepository::class)]
class Desire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'desires')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $url = null;

    #[ORM\Column]
    private ?int $priority = null;

    #[ORM\Column(type: "string", enumType: DesireState::class)]
    private DesireState $state;

    #[ORM\OneToMany(mappedBy: 'desire', targetEntity: Reservation::class)]
    private Collection $reservations;

    #[ORM\Column]
    private ?bool $exclusive = null;

    #[ORM\Column]
    private ?bool $exactly = null;

    #[ORM\ManyToMany(targetEntity: DesireList::class, mappedBy: 'desires')]
    private Collection $desireLists;

    public function __construct()
    {
        $this->reservers = new ArrayCollection();
        $this->reservations = new ArrayCollection();
        $this->desireLists = new ArrayCollection();
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function getPriority(): ?int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): static
    {
        $this->priority = $priority;

        return $this;
    }

    public function getState(): ?DesireState
    {
        return $this->state;
    }

    public function setState(DesireState|string $state): static
    {
        if (is_string($state)){
            $this->state = DesireState::from($state);
            return $this;
        }
        $this->state = $state;

        return $this;
    }

    /**
     * @return Collection<int, Reservation>
     */
    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    public function addReservation(Reservation $reservation): static
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations->add($reservation);
            $reservation->setDesire($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): static
    {
        if ($this->reservations->removeElement($reservation)) {
            // set the owning side to null (unless already changed)
            if ($reservation->getDesire() === $this) {
                $reservation->setDesire(null);
            }
        }

        return $this;
    }

    public function isExclusive(): ?bool
    {
        return $this->exclusive;
    }

    public function setExclusive(bool $exclusive): static
    {
        $this->exclusive = $exclusive;

        return $this;
    }

    public function isExactly(): ?bool
    {
        return $this->exactly;
    }

    public function setExactly(bool $exactly): static
    {
        $this->exactly = $exactly;

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
            $desireList->addDesire($this);
        }

        return $this;
    }

    public function removeDesireList(DesireList $desireList): static
    {
        if ($this->desireLists->removeElement($desireList)) {
            $desireList->removeDesire($this);
        }

        return $this;
    }
}