<?php
declare(strict_types=1);

namespace App\Content\SecretSanta\Secret;

use App\Content\SecretSanta\Secret\Data\SecretData;
use App\Entity\Secret;

/**
 * @author Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2023 DocCheck Community GmbH
 */
class SecretService
{
    public function __construct(private readonly SecretRepository $repository, private readonly SecretFactory $factory)
    {
    }

    public function createByData(SecretData $data, $flush = true): Secret
    {
        $secret = $this->factory->createByData($data);
        $this->repository->save($secret, $flush);
        return $secret;
    }

    public function update(Secret $secret, SecretData $data): Secret
    {
        $secret = $this->factory->mapData($data, $secret);
        $this->repository->save($secret);
        return $secret;
    }

    /**
     * @return Secret[]
     */
    public function findBy(array $conditions): array
    {
        return $this->repository->findBy($conditions);
    }

    public function getStatistic(\App\Entity\SecretSantaEvent $event)
    {
        return $this->repository->getStatistic($event);
    }
}
