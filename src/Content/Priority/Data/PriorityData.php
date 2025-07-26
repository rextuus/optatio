<?php
declare(strict_types=1);

namespace App\Content\Priority\Data;

use App\Entity\Desire;
use App\Entity\DesireList;
use App\Entity\Priority;


class PriorityData
{
    private Desire $desire;
    private DesireList $desireList;
    private int $value;

    public function getDesire(): Desire
    {
        return $this->desire;
    }

    public function setDesire(Desire $desire): PriorityData
    {
        $this->desire = $desire;
        return $this;
    }

    public function getDesireList(): DesireList
    {
        return $this->desireList;
    }

    public function setDesireList(DesireList $desireList): PriorityData
    {
        $this->desireList = $desireList;
        return $this;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function setValue(int $value): PriorityData
    {
        $this->value = $value;
        return $this;
    }

    public function initFromEntity(Priority $priority): PriorityData
    {
        $this->setDesire($priority->getDesire());
        $this->setDesireList($priority->getDesireList());
        $this->setValue($priority->getValue());

        return $this;
    }
}
