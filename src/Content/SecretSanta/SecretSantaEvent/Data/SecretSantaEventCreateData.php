<?php
declare(strict_types=1);

namespace App\Content\SecretSanta\SecretSantaEvent\Data;

use App\Content\SecretSanta\SecretSantaState;
use App\Entity\User;

/**
 * @author Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2023 DocCheck Community GmbH
 */
class SecretSantaEventCreateData
{
    private string $name;
    private string $firstRoundName;
    private string $secondRoundName;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): SecretSantaEventCreateData
    {
        $this->name = $name;
        return $this;
    }

    public function getFirstRoundName(): string
    {
        return $this->firstRoundName;
    }

    public function setFirstRoundName(string $firstRoundName): SecretSantaEventCreateData
    {
        $this->firstRoundName = $firstRoundName;
        return $this;
    }

    public function getSecondRoundName(): string
    {
        return $this->secondRoundName;
    }

    public function setSecondRoundName(string $secondRoundName): SecretSantaEventCreateData
    {
        $this->secondRoundName = $secondRoundName;
        return $this;
    }
}
