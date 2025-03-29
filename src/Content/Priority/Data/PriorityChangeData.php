<?php

declare(strict_types=1);

namespace App\Content\Priority\Data;

/**
 * @author Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2025 DocCheck Community GmbH
 */
class PriorityChangeData
{
    private int $priority;

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): PriorityChangeData
    {
        $this->priority = $priority;
        return $this;
    }
}
