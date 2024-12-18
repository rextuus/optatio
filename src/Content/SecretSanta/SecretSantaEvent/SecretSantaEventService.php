<?php
declare(strict_types=1);

namespace App\Content\SecretSanta\SecretSantaEvent;

use App\Content\SecretSanta\SecretSantaEvent\Data\SecretSantaEventData;
use App\Entity\Event;
use App\Entity\SecretSantaEvent;
use App\Entity\User;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @author Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2023 DocCheck Community GmbH
 */
class SecretSantaEventService
{
    public function __construct(private readonly SecretSantaEventRepository $repository, private readonly SecretSantaEventFactory $factory)
    {
    }

    public function createByData(SecretSantaEventData $data): SecretSantaEvent
    {
        $secretSantaEvent = $this->factory->createByData($data);
        $this->repository->save($secretSantaEvent);
        return $secretSantaEvent;
    }

    public function update(SecretSantaEvent $secretSantaEvent, SecretSantaEventData $data): SecretSantaEvent
    {
        $secretSantaEvent = $this->factory->mapData($data, $secretSantaEvent);
        $this->repository->save($secretSantaEvent);
        return $secretSantaEvent;
    }

    /**
     * @return SecretSantaEvent[]
     */
    public function findBy(array $conditions): array
    {
        return $this->repository->findBy($conditions);
    }

    public function findByFirstOrSecondRound(Event $event): array
    {
        return $this->repository->findByFirstOrSecondRound($event);
    }

    public function findSecretSantaEvents(?User $user = null)
    {
        return $this->repository->findSecretSantaEvents($user);
    }
}
