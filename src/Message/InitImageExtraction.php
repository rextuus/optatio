<?php

namespace App\Message;

final class InitImageExtraction
{

    public function __construct(private readonly int $desireId)
    {
    }

    public function getDesireId(): int
    {
        return $this->desireId;
    }
}
