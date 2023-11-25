<?php

namespace App\Entity;

use App\Content\User\AccessRoleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AccessRoleRepository::class)]
class AccessRole
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'accessRoles')]
    private Collection $user;

    #[ORM\Column(length: 255)]
    private ?string $ident = null;

    #[ORM\ManyToMany(targetEntity: DesireList::class, inversedBy: 'accessRoles')]
    private Collection $desireLists;

    public function __construct()
    {
        $this->user = new ArrayCollection();
        $this->desireLists = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUser(): Collection
    {
        return $this->user;
    }

    public function addUser(User $user): static
    {
        if (!$this->user->contains($user)) {
            $this->user->add($user);
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        $this->user->removeElement($user);

        return $this;
    }

    public function getIdent(): ?string
    {
        return $this->ident;
    }

    public function setIdent(string $ident): static
    {
        $this->ident = $ident;

        return $this;
    }

    /**
     * @return Collection<int, DesireList>
     */
    public function getDesireList(): Collection
    {
        return $this->desireLists;
    }

    public function addDesireList(DesireList $desireList): static
    {
        if (!$this->desireLists->contains($desireList)) {
            $this->desireLists->add($desireList);
        }

        return $this;
    }

    public function removeDesireList(DesireList $desireList): static
    {
        $this->desireLists->removeElement($desireList);

        return $this;
    }
}
