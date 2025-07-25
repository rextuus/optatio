<?php

namespace App\Entity;

use App\Content\Desire\ImageExtraction\ExtractedDesireImageCollectionRepository;
use App\Content\Desire\ImageExtraction\PicsExtractionState;
use DateTime;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ExtractedDesireImageCollectionRepository::class)]
class ExtractedDesireImageCollection
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: "string", enumType: PicsExtractionState::class)]
    private PicsExtractionState $status = PicsExtractionState::PENDING;

    #[ORM\Column(length: 3000)]
    private ?string $url = null;

    #[ORM\Column(type: Types::JSON)]
    private array $images = [];

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?DateTimeInterface $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'extractedDesireImageCollections')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Desire $desire = null;

    #[ORM\Column(length: 255)]
    private ?string $extractionId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $projectId = null;

    public function __construct()
    {
        $this->createdAt = new DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatus(): PicsExtractionState
    {
        return $this->status;
    }

    public function setStatus(PicsExtractionState $status): static
    {
        $this->status = $status;

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

    public function getImages(): array
    {
        return $this->images;
    }

    public function setImages(array $images): static
    {
        $this->images = $images;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

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

    public function getExtractionId(): ?string
    {
        return $this->extractionId;
    }

    public function setExtractionId(string $extractionId): static
    {
        $this->extractionId = $extractionId;

        return $this;
    }

    public function getProjectId(): ?string
    {
        return $this->projectId;
    }

    public function setProjectId(?string $projectId): static
    {
        $this->projectId = $projectId;

        return $this;
    }
}