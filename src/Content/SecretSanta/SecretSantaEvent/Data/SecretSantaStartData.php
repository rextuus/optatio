<?php
declare(strict_types=1);

namespace App\Content\SecretSanta\SecretSantaEvent\Data;


class SecretSantaStartData
{
    private string $checkSum;

    public function getCheckSum(): string
    {
        return $this->checkSum;
    }

    public function setCheckSum(string $checkSum): SecretSantaStartData
    {
        $this->checkSum = $checkSum;
        return $this;
    }
}
