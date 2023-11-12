<?php
declare(strict_types=1);

namespace App\Content\Desire;

use App\Content\Desire\Data\DesireData;
use App\Entity\Desire;

/**
 * @author Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2023 DocCheck Community GmbH
 */
class DesireFactory
{
    public function createByData(DesireData $data): Desire
    {
        $desire = $this->createNewInstance();
        $this->mapData($data, $desire);
        return $desire;
    }

    public function mapData(DesireData $data, Desire $desire): Desire
    {
        $desire->setName($data->getName());
        $desire->setState($data->getState());
        $desire->setUrl($data->getUrl());
        $desire->setPriority($data->getPriority());
        $desire->setOwner($data->getOwner());
        $desire->setExactly($data->isExactly());
        $desire->setExclusive($data->isExclusive());

        return $desire;
    }

    private function createNewInstance(): Desire
    {
        return new Desire();
    }
}
