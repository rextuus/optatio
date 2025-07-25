<?php

declare(strict_types=1);

namespace App\Content\Desire\ImageExtraction;

class PicsExtractionResult
{
    private string $id;
    private ?string $projectId = null;
    private PicsExtractionState $status;
    private array $images = [];
    private ?string $url = null;
    private ?string $error = null;

    public static function fromArray(array $data): self
    {
        $result = new self();

        $result->id = $data['id'] ?? '';
        $result->projectId = $data['project_id'] ?? null;
        $result->status = PicsExtractionState::tryFrom($data['status']) ?? PicsExtractionState::PENDING;
        $result->images = $data['images'] ?? [];
        $result->url = $data['url'] ?? null;
        $result->error = $data['error'] ?? null;

        return $result;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getProjectId(): ?string
    {
        return $this->projectId;
    }

    public function getStatus(): PicsExtractionState
    {
        return $this->status;
    }

    public function isComplete(): bool
    {
        return $this->status === PicsExtractionState::DONE;
    }

    public function getImages(): array
    {
        return $this->images;
    }

    public function getFirstImageUrl(): ?string
    {
        if (!empty($this->images) && isset($this->images[0]['url'])) {
            return $this->images[0]['url'];
        }

        return null;
    }

    public function getFirstJpgImageUrl(): ?string
    {
        if (empty($this->images)) {
            return null;
        }

        foreach ($this->images as $image) {
            if (isset($image['url'])) {
                $url = $image['url'];
                // Check if the URL ends with .jpg or contains .jpg? (for query parameters)
                if (preg_match('/\.jpe?g($|\?)/', strtolower($url))) {
                    return $url;
                }
            }
        }

        // If no JPG found, fall back to the first image
        return $this->getFirstImageUrl();
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function getError(): ?string
    {
        return $this->error;
    }

    public function hasError(): bool
    {
        return $this->error !== null;
    }
}
