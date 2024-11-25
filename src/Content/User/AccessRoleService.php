<?php
declare(strict_types=1);

namespace App\Content\User;

use App\Content\DesireList\DesireListRepository;
use App\Content\DesireList\DesireListService;
use App\Content\Event\EventType;
use App\Entity\AccessRole;
use App\Entity\DesireList;
use App\Entity\Event;
use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2023 DocCheck Community GmbH
 */
class AccessRoleService
{
    private const SECRET_ACCESS_ROLE = 'ROLE_SECRET_FOR_USER_';
    private const EVENT_ACCESS_ROLE = 'EVENT_';
    private const REGEX_FOR_SECRET = '^ROLE_SECRET_FOR_USER_(\d*)_EVENT_(\d*)?';

    public function __construct(
        private readonly AccessRoleRepository   $accessRoleRepository,
        private readonly EntityManagerInterface $entityManager
    )
    {
    }

    public function getSecretAccessRoleIdentByReceiverAndEvent(User $receiver, Event $event): string
    {
        return sprintf(
            '%s%d_%s%d',
            self::SECRET_ACCESS_ROLE,
            $receiver->getId(),
            self::EVENT_ACCESS_ROLE,
            $event->getId(),
        );
    }

    public function addSecretRoleToProvider(User $provider, User $receiver, Event $event): void
    {
        $this->addRoleToEntity($provider, $this->getSecretAccessRoleIdentByReceiverAndEvent($receiver, $event));
    }

    public function addSecretRoleToDesireList(DesireList $desireList, User $receiver, Event $event): void
    {
        $this->addRoleToEntity($desireList, $this->getSecretAccessRoleIdentByReceiverAndEvent($receiver, $event));
    }

    public function addRoleToEntity(HasAccessRoleInterface $entity, string $roleIdent): void
    {
        $role = $this->accessRoleRepository->findBy(['ident' => $roleIdent]);
        if (count($role) === 1) {
            $accessRole = $role[0];
        } else {
            $accessRole = new AccessRole();
            $accessRole->setIdent($roleIdent);
            $this->accessRoleRepository->save($accessRole);
        }

        $entity->addAccessRole($accessRole);
//        $a = array_map(
//            function ($entity){
//                return $entity->getIdent();
//            },
//            $entity->getAccessRoles()->toArray()
//        );
//        dump($a);
        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }

    public function checkDesireListAccess(User $user, DesireList $desireList): bool
    {
        $event = $desireList->getEvents()->first();

        $userIsPartOfEvent = $this->checkUserIsParticipantOfEvent($user, $event);
        if (!$userIsPartOfEvent){
            return false;
        }

        $desireListIdents = $desireList->getAccessRoles()->map(
            function (AccessRole $accessRole) {
                return $accessRole->getIdent();
            }
        )->toArray();
        $userIdents = $user->getAccessRoles()->map(
            function (AccessRole $accessRole) {
                return $accessRole->getIdent();
            }
        )->toArray();

        // check if its own list
        if (in_array('USER_'.$user->getId(), $desireListIdents)){
            return true;
        }

        // check if user has access via secret
        $secretIdent = 'secretIdent';
        $ownerId = -1;
        foreach ($desireListIdents as $ident){
            preg_match('~' . self::REGEX_FOR_SECRET . '~', $ident, $matches);
            if ($matches) {
                $secretIdent = $matches[0];
                $ownerId = $matches[1];
            }
        }

        $userId = -2;
        foreach ($userIdents as $ident){
            preg_match('~^USER_(\d*)?~', $ident, $matches);
            if ($matches){
                $userId = $matches[1];
            }
        }

        $userIsOwner = $ownerId === $userId;
        $userIsSecret = in_array($secretIdent, $userIdents);

        $shared = array_intersect($desireListIdents, $userIdents);
        if (count($shared) && ($userIsOwner || $userIsSecret) > 0){
            return true;
        }

        // last chance: if no ss event you only need to be participant
        if ($event->getEventType() !== EventType::SECRET_SANTA && count($shared)){
            return true;
        }

        return false;
    }

    private function checkUserIsParticipantOfEvent(User $user, Event $targetEvent): bool
    {
        return count($user->getEvents()->filter(
                function (Event $event) use ($targetEvent) {
                    return $event->getId() === $targetEvent->getId();
                }
            )) > 0;
    }
}
