<?php
declare(strict_types=1);

namespace App\Content\SecretSanta\SecretSantaEvent\Data;

/**
 * @author Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2023 DocCheck Community GmbH
 */
class SecretSantaEventJoinData
{
    private bool $firstRound;
    private bool $secondRound;

    public function isFirstRound(): bool
    {
        return $this->firstRound;
    }

    public function setFirstRound(bool $firstRound): SecretSantaEventJoinData
    {
        $this->firstRound = $firstRound;
        return $this;
    }

    public function isSecondRound(): bool
    {
        return $this->secondRound;
    }

    public function setSecondRound(bool $secondRound): SecretSantaEventJoinData
    {
        $this->secondRound = $secondRound;
        return $this;
    }
}
