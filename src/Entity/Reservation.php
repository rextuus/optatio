<?php

namespace App\Entity;

use App\Content\Reservation\ReservationRepository;
use App\Content\Reservation\ReservationState;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReservationRepository::class)]
class Reservation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'reservations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    #[ORM\ManyToOne(inversedBy: 'reservations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Desire $desire = null;

    #[ORM\Column(type: "string", enumType: ReservationState::class)]
    private ReservationState $state;

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

    public function getDesire(): ?Desire
    {
        return $this->desire;
    }

    public function setDesire(?Desire $desire): static
    {
        $this->desire = $desire;

        return $this;
    }

    public function getState(): ReservationState
    {
        return $this->state;
    }

    public function setState(ReservationState $state): Reservation
    {
        $this->state = $state;
        return $this;
    }
}
