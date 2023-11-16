<?php
declare(strict_types=1);

namespace App\Content\DesireList;

use App\Content\DesireList\Data\DesireListData;
use App\Entity\DesireList;
use App\Entity\Event;
use App\Entity\User;

/**
 * @author Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2023 DocCheck Community GmbH
 */
class DesireListService
{
    public function __construct(private readonly DesireListRepository $repository, private readonly DesireListFactory $factory)
    {
    }

    public function createByData(DesireListData $data): DesireList
    {
        $desireList = $this->factory->createByData($data);
        $this->repository->save($desireList);
        return $desireList;
    }

    public function update(DesireList $desireList, DesireListData $data): DesireList
    {
        $desireList = $this->factory->mapData($data, $desireList);
        $this->repository->save($desireList);
        return $desireList;
    }

    /**
     * @return DesireList[]
     */
    public function findBy(array $conditions): array
    {
        return $this->repository->findBy($conditions);
    }

    /**
     * @return DesireList[]
     */
    public function findByUserAndEvent(User $user, Event $event): array
    {
        return $this->repository->findByUserAndEvent($user, $event);
    }
}
