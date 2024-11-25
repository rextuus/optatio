<?php
declare(strict_types=1);

namespace App\Content\SecretSanta\SecretSantaEvent;

use App\Content\SecretSanta\SecretSantaEvent\Data\SecretSantaEventData;
use App\Entity\SecretSantaEvent;

/**
 * @author Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2023 DocCheck Community GmbH
 */
class SecretSantaEventFactory
{
    public function createByData(SecretSantaEventData $data): SecretSantaEvent
    {
        $secretSantaEvent = $this->createNewInstance();
        $this->mapData($data, $secretSantaEvent);
        return $secretSantaEvent;
    }

    public function mapData(SecretSantaEventData $data, SecretSantaEvent $secretSantaEvent): SecretSantaEvent
    {
        $secretSantaEvent->setName($data->getName());
        $secretSantaEvent->setState($data->getSecretSantaState());
        $secretSantaEvent->setFirstRound($data->getFirstRound());
        $secretSantaEvent->setSecondRound($data->getSecondRound());
        $secretSantaEvent->setCreator($data->getCreator());
        $secretSantaEvent->setIsDoubleRound($data->isDoubleRound());

        return $secretSantaEvent;
    }

    private function createNewInstance(): SecretSantaEvent
    {
        return new SecretSantaEvent();
    }
}
