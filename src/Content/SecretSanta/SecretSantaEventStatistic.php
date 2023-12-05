<?php
declare(strict_types=1);

namespace App\Content\SecretSanta;

/**
 * @author Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2023 DocCheck Community GmbH
 */
class SecretSantaEventStatistic
{
    private int $firstRoundRetrieved = 0;
    private int $secondRoundRetrieved = 0;
    private int $firstRoundNonRetrieved = 0;
    private int $secondRoundNonRetrieved = 0;
    private int $desiresTotal = 0;
    private int $desiresReserved = 0;
    private int $userWithoutDesires = 0;

    public function getFirstRoundRetrieved(): int
    {
        return $this->firstRoundRetrieved;
    }

    public function setFirstRoundRetrieved(int $firstRoundRetrieved): SecretSantaEventStatistic
    {
        $this->firstRoundRetrieved = $firstRoundRetrieved;
        return $this;
    }

    public function getSecondRoundRetrieved(): int
    {
        return $this->secondRoundRetrieved;
    }

    public function setSecondRoundRetrieved(int $secondRoundRetrieved): SecretSantaEventStatistic
    {
        $this->secondRoundRetrieved = $secondRoundRetrieved;
        return $this;
    }

    public function getFirstRoundNonRetrieved(): int
    {
        return $this->firstRoundNonRetrieved;
    }

    public function setFirstRoundNonRetrieved(int $firstRoundNonRetrieved): SecretSantaEventStatistic
    {
        $this->firstRoundNonRetrieved = $firstRoundNonRetrieved;
        return $this;
    }

    public function getSecondRoundNonRetrieved(): int
    {
        return $this->secondRoundNonRetrieved;
    }

    public function setSecondRoundNonRetrieved(int $secondRoundNonRetrieved): SecretSantaEventStatistic
    {
        $this->secondRoundNonRetrieved = $secondRoundNonRetrieved;
        return $this;
    }

    public function getDesiresTotal(): int
    {
        return $this->desiresTotal;
    }

    public function setDesiresTotal(int $desiresTotal): SecretSantaEventStatistic
    {
        $this->desiresTotal = $desiresTotal;
        return $this;
    }

    public function getDesiresReserved(): int
    {
        return $this->desiresReserved;
    }

    public function setDesiresReserved(int $desiresReserved): SecretSantaEventStatistic
    {
        $this->desiresReserved = $desiresReserved;
        return $this;
    }

    public function getUserWithoutDesires(): int
    {
        return $this->userWithoutDesires;
    }

    public function setUserWithoutDesires(int $userWithoutDesires): SecretSantaEventStatistic
    {
        $this->userWithoutDesires = $userWithoutDesires;
        return $this;
    }
}
