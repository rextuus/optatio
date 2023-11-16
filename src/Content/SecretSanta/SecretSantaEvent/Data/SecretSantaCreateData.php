<?php
declare(strict_types=1);

namespace App\Content\SecretSanta\SecretSantaEvent\Data;

/**
 * @author Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2023 DocCheck Community GmbH
 */
class SecretSantaCreateData
{
    private string $name;
    private string $nameFirst;
    private string $nameSecond;
    private bool $second;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): SecretSantaCreateData
    {
        $this->name = $name;
        return $this;
    }

    public function isSecond(): bool
    {
        return $this->second;
    }

    public function setSecond(bool $second): SecretSantaCreateData
    {
        $this->second = $second;
        return $this;
    }

    public function getNameFirst(): string
    {
        return $this->nameFirst;
    }

    public function setNameFirst(string $nameFirst): SecretSantaCreateData
    {
        $this->nameFirst = $nameFirst;
        return $this;
    }

    public function getNameSecond(): string
    {
        return $this->nameSecond;
    }

    public function setNameSecond(string $nameSecond): SecretSantaCreateData
    {
        $this->nameSecond = $nameSecond;
        return $this;
    }
}
