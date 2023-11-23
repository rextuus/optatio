<?php
declare(strict_types=1);

namespace App\Content\Priority;

use App\Content\Priority\Data\PriorityData;
use App\Entity\DesireList;
use App\Entity\Priority;

/**
 * @author Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2023 DocCheck Community GmbH
 */
class PriorityService
{
    public function __construct(private readonly PriorityRepository $repository, private readonly PriorityFactory $factory)
    {
    }

    public function createByData(PriorityData $data): Priority
    {
        $priority = $this->factory->createByData($data);
        $this->repository->save($priority);
        return $priority;
    }

    public function update(Priority $priority, PriorityData $data): Priority
    {
        $priority = $this->factory->mapData($data, $priority);
        $this->repository->save($priority);
        return $priority;
    }

    /**
     * @return Priority[]
     */
    public function findBy(array $conditions): array
    {
        return $this->repository->findBy($conditions);
    }

    public function getHighestPriorityByList(DesireList $desireList): int
    {
        $max = $this->repository->getHighestPriorityByList($desireList);
        if (is_null($max)) {
            return 0;
        }
        return $max;
    }
}
