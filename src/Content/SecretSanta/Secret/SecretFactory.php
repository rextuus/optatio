<?php
declare(strict_types=1);

namespace App\Content\SecretSanta\Secret;

use App\Content\SecretSanta\Secret\Data\SecretData;
use App\Entity\Secret;


class SecretFactory
{
    public function createByData(SecretData $data): Secret
    {
        $secret = $this->createNewInstance();
        $this->mapData($data, $secret);
        return $secret;
    }

    public function mapData(SecretData $data, Secret $secret): Secret
    {
        $secret->setProvider($data->getProvider());
        $secret->setReceiver($data->getReceiver());
        $secret->setEvent($data->getEvent());
        $secret->setSecretSantaEvent($data->getSecretSantaEvent());
        $secret->setRetrieved($data->isRetrieved());

        return $secret;
    }

    private function createNewInstance(): Secret
    {
        return new Secret();
    }
}
