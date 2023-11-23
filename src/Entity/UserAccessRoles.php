<?php

namespace App\Entity;

use App\Content\User\UserAccessRolesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserAccessRolesRepository::class)]
class UserAccessRoles
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'userAccessRoles', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(type: Types::JSON)]
    private Collection $roles;

    public function __construct()
    {
        $this->roles = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getRoles(): Collection
    {
        return $this->roles;
    }

    public function setRoles(Collection $roles): UserAccessRoles
    {
        $this->roles = $roles;
        return $this;
    }

    public function addRole(string $participant): static
    {
        if (!$this->roles->contains($participant)) {
            $this->roles->add($participant);
        }

        return $this;
    }

    public function removeRole(string $participant): static
    {
        $this->roles->removeElement($participant);

        return $this;
    }
}
