<?php

declare(strict_types=1);

namespace App\Message;

class CheckDesireImageExtractionStatus
{
    public function __construct(
        private readonly int $extractedDesireImageCollectionId,
    ) {
    }

    public function getExtractedDesireImageCollectionId(): int
    {
        return $this->extractedDesireImageCollectionId;
    }
}
