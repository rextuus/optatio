<?php
declare(strict_types=1);

namespace App\Content\SecretSanta\Exclusion;

use App\Content\SecretSanta\Exclusion\Data\ExclusionData;
use App\Entity\Exclusion;

/**
 * @author Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2023 DocCheck Community GmbH
 */
class ExclusionFactory
{
    public function createByData(ExclusionData $data): Exclusion
    {
        $exclusion = $this->createNewInstance();
        $this->mapData($data, $exclusion);
        return $exclusion;
    }

    public function mapData(ExclusionData $data, Exclusion $exclusion): Exclusion
    {
        $exclusion->setExclusionCreator($data->getExclusionCreator());
        $exclusion->setExcludedUser($data->getExcludedUser());
        $exclusion->setEvent($data->getEvent());
        $exclusion->setBidirectional($data->isBidirectional());

        return $exclusion;
    }

    private function createNewInstance(): Exclusion
    {
        return new Exclusion();
    }
}
