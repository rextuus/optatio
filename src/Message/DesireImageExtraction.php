<?php

declare(strict_types=1);

namespace App\Message;

class DesireImageExtraction
{
    public function __construct(
        private readonly int $desireId,
    ) {
    }

    public function getDesireId(): int
    {
        return $this->desireId;
    }
}
