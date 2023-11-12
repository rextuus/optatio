<?php
declare(strict_types=1);

namespace App\Content\Desire\Data;

use App\Content\Desire\DesireState;
use App\Entity\Desire;
use App\Entity\User;

/**
 * @author Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2023 DocCheck Community GmbH
 */
class DesireData
{
    private User $owner;
    private ?string $name;
    private ?string $url;
    private int $priority;
    private DesireState $state;
    private bool $exactly;
    private bool $exclusive;

    public function getOwner(): User
    {
        return $this->owner;
    }

    public function setOwner(User $owner): DesireData
    {
        $this->owner = $owner;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): DesireData
    {
        $this->name = $name;
        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): DesireData
    {
        $this->url = $url;
        return $this;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): DesireData
    {
        $this->priority = $priority;
        return $this;
    }

    public function getState(): DesireState
    {
        return $this->state;
    }

    public function setState(DesireState $state): DesireData
    {
        $this->state = $state;
        return $this;
    }

    public function isExactly(): bool
    {
        return $this->exactly;
    }

    public function setExactly(bool $exactly): DesireData
    {
        $this->exactly = $exactly;
        return $this;
    }

    public function isExclusive(): bool
    {
        return $this->exclusive;
    }

    public function setExclusive(bool $exclusive): DesireData
    {
        $this->exclusive = $exclusive;
        return $this;
    }

    public function initFromEntity(Desire $desire): DesireData
    {
        $this->setOwner($desire->getOwner());
        $this->setName($desire->getName());
        $this->setState($desire->getState());
        $this->setUrl($desire->getUrl());
        $this->setExactly($desire->isExactly());
        $this->setExclusive($desire->isExclusive());

        return $this;
    }
}
