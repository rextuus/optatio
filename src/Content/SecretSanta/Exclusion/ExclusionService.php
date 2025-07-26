<?php
declare(strict_types=1);

namespace App\Content\SecretSanta\Exclusion;

use App\Content\SecretSanta\Exclusion\Data\ExclusionData;
use App\Entity\Exclusion;


class ExclusionService
{
    public function __construct(private readonly ExclusionRepository $repository, private readonly ExclusionFactory $factory)
    {
    }

    public function createByData(ExclusionData $data): Exclusion
    {
        $exclusion = $this->factory->createByData($data);
        $this->repository->save($exclusion);
        return $exclusion;
    }

    public function update(Exclusion $exclusion, ExclusionData $data): Exclusion
    {
        $exclusion = $this->factory->mapData($data, $exclusion);
        $this->repository->save($exclusion);
        return $exclusion;
    }

    /**
     * @return Exclusion[]
     */
    public function findBy(array $conditions): array
    {
        return $this->repository->findBy($conditions);
    }

    public function delete(Exclusion $exclusion): void
    {
        $this->repository->delete($exclusion);
    }
}
