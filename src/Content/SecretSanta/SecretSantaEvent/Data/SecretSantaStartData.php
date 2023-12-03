<?php
declare(strict_types=1);

namespace App\Content\SecretSanta\SecretSantaEvent\Data;

/**
 * @author Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2023 DocCheck Community GmbH
 */
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
