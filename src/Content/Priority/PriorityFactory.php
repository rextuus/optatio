<?php
declare(strict_types=1);

namespace App\Content\Priority;

use App\Content\Priority\Data\PriorityData;
use App\Entity\Priority;

/**
 * @author Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2023 DocCheck Community GmbH
 */
class PriorityFactory
{
    public function createByData(PriorityData $data): Priority
    {
        $priority = $this->createNewInstance();
        $this->mapData($data, $priority);
        return $priority;
    }

    public function mapData(PriorityData $data, Priority $priority): Priority
    {
        $priority->setDesire($data->getDesire());
        $priority->setDesireList($data->getDesireList());
        $priority->setValue($data->getValue());

        return $priority;
    }

    private function createNewInstance(): Priority
    {
        return new Priority();
    }
}
