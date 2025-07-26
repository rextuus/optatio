<?php
declare(strict_types=1);

namespace App\Content\SecretSanta\Calculation;


class PotentialSecret
{
    private int|null $provider;
    private int|null $receiver;

    public function __construct(?int $provider, ?int $receiver)
    {
        $this->provider = $provider;
        $this->receiver = $receiver;
    }

    public function isFaulty(): bool
    {
        return is_null($this->provider) || is_null($this->receiver);
    }

    public function getProvider(): ?int
    {
        return $this->provider;
    }

    public function getReceiver(): ?int
    {
        return $this->receiver;
    }
}
