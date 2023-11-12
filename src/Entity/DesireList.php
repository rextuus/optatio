<?php

namespace App\Entity;

use App\Content\DesireList\DesireListRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DesireListRepository::class)]
class DesireList
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

    #[ORM\Column(type: Types::ARRAY)]
    private array $accessRoles = [];

    public function __construct()
    {
        $this->desires = new ArrayCollection();
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
