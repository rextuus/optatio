<?php
declare(strict_types=1);

namespace App\Content\Priority;

use App\Content\Priority\Data\PriorityData;
use App\Entity\Desire;
use App\Entity\DesireList;
use App\Entity\Priority;
use Exception;

/**
 * @author Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2023 DocCheck Community GmbH
 */
class PriorityService
{
    public function __construct(private readonly PriorityRepository $repository, private readonly PriorityFactory $factory)
    {
    }

    public function createByData(PriorityData $data, bool $flush = true): Priority
    {
        $priority = $this->factory->createByData($data);
        $this->repository->save($priority, $flush);
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

    /**
     * @throws NoPriorityException
     * @throws ToMuchPrioritiesException
     */
    public function getUniquePriorityByList(DesireList $desireList, Desire $desire): Priority
    {
        $priorities = $this->findBy(['desireList' => $desireList, 'desire' => $desire]);
        if (count($priorities) === 0) {
            throw new NoPriorityException('No priority found for desire/desireList combination');
        }

        if (count($priorities) > 1) {
            throw new ToMuchPrioritiesException('No unique priority found for desire/desireList combination');
        }

        return $priorities[0];
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
