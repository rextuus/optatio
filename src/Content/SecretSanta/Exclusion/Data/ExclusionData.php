<?php
declare(strict_types=1);

namespace App\Content\SecretSanta\Exclusion\Data;

use App\Entity\Exclusion;
use App\Entity\SecretSantaEvent;
use App\Entity\User;


class ExclusionData
{
    private SecretSantaEvent $event;

    private User $exclusionCreator;

    private ?User $excludedUser = null;
    private bool $bidirectional;

    public function getEvent(): SecretSantaEvent
    {
        return $this->event;
    }

    public function setEvent(SecretSantaEvent $event): ExclusionData
    {
        $this->event = $event;
        return $this;
    }

    public function getExclusionCreator(): User
    {
        return $this->exclusionCreator;
    }

    public function setExclusionCreator(User $exclusionCreator): ExclusionData
    {
        $this->exclusionCreator = $exclusionCreator;
        return $this;
    }

    public function getExcludedUser(): ?User
    {
        return $this->excludedUser;
    }

    public function setExcludedUser(User $excludedUser): ExclusionData
    {
        $this->excludedUser = $excludedUser;
        return $this;
    }

    public function isBidirectional(): bool
    {
        return $this->bidirectional;
    }

    public function setBidirectional(bool $bidirectional): ExclusionData
    {
        $this->bidirectional = $bidirectional;
        return $this;
    }

    public function initFromEntity(Exclusion $exclusion): ExclusionData
    {
        $this->setExcludedUser($exclusion->getExcludedUser());
        $this->setExclusionCreator($exclusion->getExclusionCreator());
        $this->setEvent($exclusion->getEvent());
        $this->setBidirectional($exclusion->isBidirectional());

        return $this;
    }
}
