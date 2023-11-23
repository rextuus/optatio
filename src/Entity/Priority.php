<?php

namespace App\Entity;

use App\Content\Priority\PriorityRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PriorityRepository::class)]
class Priority
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'priorities')]
    #[ORM\JoinColumn(nullable: false)]
    private ?DesireList $desireList = null;

    #[ORM\ManyToOne(inversedBy: 'priorities')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Desire $desire = null;

    #[ORM\Column]
    private ?int $value = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDesireList(): ?DesireList
    {
        return $this->desireList;
    }

    public function setDesireList(?DesireList $desireList): static
    {
        $this->desireList = $desireList;

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

    public function getValue(): ?int
    {
        return $this->value;
    }

    public function setValue(int $value): static
    {
        $this->value = $value;

        return $this;
    }
}
