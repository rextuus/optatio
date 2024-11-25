<?php

declare(strict_types=1);

namespace App\Content\DesireList\Data;

use App\Entity\Desire;
use App\Entity\DesireList;

/**
 * @author Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2024 DocCheck Community GmbH
 */
class DesireCopyData
{
    private DesireList $from;
    private DesireList $to;

    /**
     * @var array<Desire>
     */
    private array $desires = [];

    public function getFrom(): DesireList
    {
        return $this->from;
    }

    public function setFrom(DesireList $from): DesireCopyData
    {
        $this->from = $from;
        return $this;
    }

    public function getTo(): DesireList
    {
        return $this->to;
    }

    public function setTo(DesireList $to): DesireCopyData
    {
        $this->to = $to;
        return $this;
    }

    public function getDesires(): array
    {
        return $this->desires;
    }

    public function setDesires(array $desires): DesireCopyData
    {
        $this->desires = $desires;
        return $this;
    }
}
