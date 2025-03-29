<?php

namespace App\Entity;

use App\Content\Desire\DesireRepository;
use App\Content\Desire\DesireState;
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

    #[ORM\Column]
    private ?bool $listed = null;

    #[ORM\Column(length: 3000)]
    private ?string $description = null;

    #[ORM\OneToMany(mappedBy: 'desire', targetEntity: Priority::class)]
    private Collection $priorities;

    #[ORM\OneToMany(mappedBy: 'desire', targetEntity: Url::class)]
    private Collection $urls;

    #[ORM\OneToMany(mappedBy: 'desire', targetEntity: Image::class)]
    private Collection $images;

    public function __construct()
    {
        $this->reservers = new ArrayCollection();
        $this->reservations = new ArrayCollection();
        $this->desireLists = new ArrayCollection();
        $this->priorities = new ArrayCollection();
        $this->urls = new ArrayCollection();
        $this->images = new ArrayCollection();
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

    public function isListed(): ?bool
    {
        return $this->listed;
    }

    public function setListed(bool $listed): static
    {
        $this->listed = $listed;

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
            $priority->setDesire($this);
        }

        return $this;
    }

    public function removePriority(Priority $priority): static
    {
        if ($this->priorities->removeElement($priority)) {
            // set the owning side to null (unless already changed)
            if ($priority->getDesire() === $this) {
                $priority->setDesire(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Url>
     */
    public function getUrls(): Collection
    {
        return $this->urls;
    }

    public function addUrl(Url $url): static
    {
        if (!$this->urls->contains($url)) {
            $this->urls->add($url);
            $url->setDesire($this);
        }

        return $this;
    }

    public function removeUrl(Url $url): static
    {
        if ($this->urls->removeElement($url)) {
            // set the owning side to null (unless already changed)
            if ($url->getDesire() === $this) {
                $url->setDesire(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Image>
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(Image $image): static
    {
        if (!$this->images->contains($image)) {
            $this->images->add($image);
            $image->setDesire($this);
        }

        return $this;
    }

    public function removeImage(Image $image): static
    {
        if ($this->images->removeElement($image)) {
            // set the owning side to null (unless already changed)
            if ($image->getDesire() === $this) {
                $image->setDesire(null);
            }
        }

        return $this;
    }

    public function getPriorityByList(?DesireList $list): Priority|null
    {
        foreach ($this->getPriorities() as $priority) {
            if ($priority->getDesireList()->getId() === $list->getId()) {
                return $priority;
            }
        }

        return null;
    }
}
