<?php
declare(strict_types=1);

namespace App\Content\DesireList;

use App\Content\Desire\Data\DesireData;
use App\Content\DesireList\Data\DesireListData;
use App\Entity\Desire;
use App\Entity\DesireList;
use App\Entity\Event;
use App\Entity\User;
use Symfony\Component\Security\Core\User\UserInterface;

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

    /**
     * @param Event[] $events
     * @return DesireList[]
     */
    public function findByUserAndEvents(User $user, array $events): array
    {
        return $this->repository->findByUserAndEvents($user, $events);
    }

    public function addDesireToList(Desire $desire, DesireList $desireList, bool $save = true): void
    {
        $desireList->addDesire($desire);
        if ($save) {
            $this->repository->save($desireList);
        }
    }

    /**
     * @param array<Desire> $desires
     */
    public function shareDesiresBetweenLists(DesireList $targetList, array $desires): void
    {
        foreach ($desires as $desire) {
            $this->addDesireToList($desire, $targetList, false);
        }

        $this->repository->save($targetList);
    }
}
