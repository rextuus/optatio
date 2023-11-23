<?php
declare(strict_types=1);

namespace App\Content\Desire;

use App\Content\Desire\Data\DesireData;
use App\Entity\Desire;
use App\Entity\DesireList;

/**
 * @author Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2023 DocCheck Community GmbH
 */
class DesireService
{
    public function __construct(private readonly DesireRepository $repository, private readonly DesireFactory $factory)
    {
    }

    public function createByData(DesireData $data): Desire
    {
        $desire = $this->factory->createByData($data);
        $this->repository->save($desire);
        return $desire;
    }

    public function update(Desire $desire, DesireData $data): Desire
    {
        $desire = $this->factory->mapData($data, $desire);
        $this->repository->save($desire);
        return $desire;
    }

    /**
     * @return Desire[]
     */
    public function findBy(array $conditions): array
    {
        return $this->repository->findBy($conditions, ['priority' => 'DESC']);
    }

    /**
     * @return Desire[]
     */
    public function findByListOrderedByPriority(DesireList $list): array
    {
        return $this->repository->findByListOrderByPriority($list);
    }
}
