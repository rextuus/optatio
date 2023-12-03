<?php
declare(strict_types=1);

namespace App\Content\SecretSanta\Calculation;

/**
 * @author Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2023 DocCheck Community GmbH
 */
class CalculationResult
{
    /**
     * @var PotentialSecret[]
     */
    private array $round1;

    /**
     * @var PotentialSecret[]
     */
    private array $round2;

    public function __construct(array $round1, array $round2)
    {
        $this->round1 = $round1;
        $this->round2 = $round2;
    }

    public function getRound1(): array
    {
        return $this->round1;
    }

    public function getRound2(): array
    {
        return $this->round2;
    }

    public function isSuccess(): bool
    {
        dump($this->round1);
        dump($this->round2);
        return count($this->round1) > 0 && count($this->round2) > 0;
    }

    public function checkIntegrity(): bool
    {
        $round = array_merge($this->round1, $this->round2);

        $userBalance = [];
        foreach ($round as $secret){
            if (array_key_exists($secret->getReceiver(), $userBalance)) {
                $userBalance[$secret->getReceiver()] = $userBalance[$secret->getReceiver()] - 1;
            } else {
                $userBalance[$secret->getReceiver()] = -1;
            }

            if (array_key_exists($secret->getProvider(), $userBalance)) {
                $userBalance[$secret->getProvider()] = $userBalance[$secret->getProvider()] + 1;
            } else {
                $userBalance[$secret->getProvider()] = 1;
            }
        }

        foreach ($userBalance as $userBalanceEntry) {
            if ($userBalanceEntry !== 0) {
                return false;
            }
        }

        return true;
    }
}
