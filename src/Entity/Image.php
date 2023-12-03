<?php

namespace App\Entity;

use App\Repository\ImageRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ImageRepository::class)]
class Image
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['read'])]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Groups(['read'])]
    private ?string $filePath = null;

    #[ORM\ManyToOne(inversedBy: 'images')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['read'])]
    private ?DateTimeInterface $created = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['read'])]
    private ?string $cdnUrl = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['read'])]
    private ?DateTimeInterface $delivered = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['read'])]
    private ?DateTimeInterface $displayed = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['read'])]
    private ?\DateTimeInterface $uploaded = null;

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

    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    public function setFilePath(string $filePath): static
    {
        $this->filePath = $filePath;

        return $this;
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

    public function getCreated(): ?DateTimeInterface
    {
        return $this->created;
    }

    public function setCreated(DateTimeInterface $created): static
    {
        $this->created = $created;

        return $this;
    }

    public function getDisplayed(): ?DateTimeInterface
    {
        return $this->displayed;
    }

    public function setDisplayed(?DateTimeInterface $displayed): static
    {
        $this->displayed = $displayed;

        return $this;
    }

    public function getDelivered(): ?DateTimeInterface
    {
        return $this->delivered;
    }

    public function setDelivered(?DateTimeInterface $delivered): static
    {
        $this->delivered = $delivered;

        return $this;
    }

    public function getCdnUrl(): ?string
    {
        return $this->cdnUrl;
    }

    public function setCdnUrl(?string $cdnUrl): static
    {
        $this->cdnUrl = $cdnUrl;

        return $this;
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function updatedTimestamps(): void
    {
        if (is_null($this->getCreated())) {
            $this->setCreated(new DateTime('now'));
        }
    }

    public function getUploaded(): ?\DateTimeInterface
    {
        return $this->uploaded;
    }

    public function setUploaded(?\DateTimeInterface $uploaded): static
    {
        $this->uploaded = $uploaded;

        return $this;
    }
}
