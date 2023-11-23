<?php

namespace App\Entity;

use App\Content\User\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
#[ORM\HasLifecycleCallbacks]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(type: 'boolean')]
    private $isVerified = false;

    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: Desire::class)]
    private Collection $desires;

    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: Reservation::class)]
    private Collection $reservations;

    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: DesireList::class)]
    private Collection $desireLists;

    #[ORM\ManyToMany(targetEntity: Event::class, mappedBy: 'participants')]
    private Collection $events;

    #[ORM\OneToMany(mappedBy: 'creator', targetEntity: Event::class)]
    private Collection $createdEvents;

    #[ORM\Column(length: 255)]
    private ?string $firstName = null;

    #[ORM\Column(length: 255)]
    private ?string $lastName = null;

    #[ORM\OneToMany(mappedBy: 'creator', targetEntity: SecretSantaEvent::class)]
    private Collection $secretSantaEvents;

    #[ORM\OneToMany(mappedBy: 'exclusionCreator', targetEntity: Exclusion::class)]
    private Collection $exclusions;

    #[ORM\OneToMany(mappedBy: 'provider', targetEntity: Secret::class)]
    private Collection $providingSecrets;

    #[ORM\OneToMany(mappedBy: 'receiver', targetEntity: Secret::class)]
    private Collection $receivingSecrets;

    #[ORM\ManyToMany(targetEntity: AccessRole::class, mappedBy: 'user')]
    private Collection $accessRoles;

    public function __construct()
    {
        $this->desires = new ArrayCollection();
        $this->reservers = new ArrayCollection();
        $this->reservations = new ArrayCollection();
        $this->desireLists = new ArrayCollection();
        $this->events = new ArrayCollection();
        $this->createdEvents = new ArrayCollection();
        $this->secretSantaEvents = new ArrayCollection();
        $this->exclusions = new ArrayCollection();
        $this->providingSecrets = new ArrayCollection();
        $this->receivingSecrets = new ArrayCollection();
        $this->accessRoles = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

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
            $desire->setOwner($this);
        }

        return $this;
    }

    public function removeDesire(Desire $desire): static
    {
        if ($this->desires->removeElement($desire)) {
            // set the owning side to null (unless already changed)
            if ($desire->getOwner() === $this) {
                $desire->setOwner(null);
            }
        }

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
            $reservation->setOwner($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): static
    {
        if ($this->reservations->removeElement($reservation)) {
            // set the owning side to null (unless already changed)
            if ($reservation->getOwner() === $this) {
                $reservation->setOwner(null);
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
            $desireList->setOwner($this);
        }

        return $this;
    }

    public function removeDesireList(DesireList $desireList): static
    {
        if ($this->desireLists->removeElement($desireList)) {
            // set the owning side to null (unless already changed)
            if ($desireList->getOwner() === $this) {
                $desireList->setOwner(null);
            }
        }

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
            $event->addParticipant($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): static
    {
        if ($this->events->removeElement($event)) {
            $event->removeParticipant($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Event>
     */
    public function getCreatedEvents(): Collection
    {
        return $this->createdEvents;
    }

    public function addCreatedEvent(Event $createdEvent): static
    {
        if (!$this->createdEvents->contains($createdEvent)) {
            $this->createdEvents->add($createdEvent);
            $createdEvent->setCreator($this);
        }

        return $this;
    }

    public function removeCreatedEvent(Event $createdEvent): static
    {
        if ($this->createdEvents->removeElement($createdEvent)) {
            // set the owning side to null (unless already changed)
            if ($createdEvent->getCreator() === $this) {
                $createdEvent->setCreator(null);
            }
        }

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getFullName(): string
    {
        return $this->getFirstName().' '.$this->getLastName();
    }

    /**
     * @return Collection<int, SecretSantaEvent>
     */
    public function getSecretSantaEvents(): Collection
    {
        return $this->secretSantaEvents;
    }

    public function addSecretSantaEvent(SecretSantaEvent $secretSantaEvent): static
    {
        if (!$this->secretSantaEvents->contains($secretSantaEvent)) {
            $this->secretSantaEvents->add($secretSantaEvent);
            $secretSantaEvent->setCreator($this);
        }

        return $this;
    }

    public function removeSecretSantaEvent(SecretSantaEvent $secretSantaEvent): static
    {
        if ($this->secretSantaEvents->removeElement($secretSantaEvent)) {
            // set the owning side to null (unless already changed)
            if ($secretSantaEvent->getCreator() === $this) {
                $secretSantaEvent->setCreator(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Exclusion>
     */
    public function getExclusions(): Collection
    {
        return $this->exclusions;
    }

    public function addExclusion(Exclusion $exclusion): static
    {
        if (!$this->exclusions->contains($exclusion)) {
            $this->exclusions->add($exclusion);
            $exclusion->setExclusionCreator($this);
        }

        return $this;
    }

    public function removeExclusion(Exclusion $exclusion): static
    {
        if ($this->exclusions->removeElement($exclusion)) {
            // set the owning side to null (unless already changed)
            if ($exclusion->getExclusionCreator() === $this) {
                $exclusion->setExclusionCreator(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Secret>
     */
    public function getProvidingSecrets(): Collection
    {
        return $this->providingSecrets;
    }

    public function addProvidingSecret(Secret $providingSecret): static
    {
        if (!$this->providingSecrets->contains($providingSecret)) {
            $this->providingSecrets->add($providingSecret);
            $providingSecret->setProvider($this);
        }

        return $this;
    }

    public function removeProvidingSecret(Secret $providingSecret): static
    {
        if ($this->providingSecrets->removeElement($providingSecret)) {
            // set the owning side to null (unless already changed)
            if ($providingSecret->getProvider() === $this) {
                $providingSecret->setProvider(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Secret>
     */
    public function getReceivingSecrets(): Collection
    {
        return $this->receivingSecrets;
    }

    public function addReceivingSecret(Secret $receivingSecret): static
    {
        if (!$this->receivingSecrets->contains($receivingSecret)) {
            $this->receivingSecrets->add($receivingSecret);
            $receivingSecret->setReceiver($this);
        }

        return $this;
    }

    public function removeReceivingSecret(Secret $receivingSecret): static
    {
        if ($this->receivingSecrets->removeElement($receivingSecret)) {
            // set the owning side to null (unless already changed)
            if ($receivingSecret->getReceiver() === $this) {
                $receivingSecret->setReceiver(null);
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
            $accessRole->addUser($this);
        }

        return $this;
    }

    public function removeAccessRole(AccessRole $accessRole): static
    {
        if ($this->accessRoles->removeElement($accessRole)) {
            $accessRole->removeUser($this);
        }

        return $this;
    }
}
