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

    public function checkDesireListAccess(User $user, DesireList $desireList, Event $event): bool
    {
        $debug = sprintf(
            'User %s (%s) is trying to access List %s (%s) of owner %s (%s) and event %s (%s)',
            $user->getId(),
            $user->getFullName(),
            $desireList->getId(),
            $desireList->getName(),
            $desireList->getOwner()->getId(),
            $desireList->getOwner()->getFullName(),
            $event->getId(),
            $event->getName()
        );
        dump($debug);
//        $event = $desireList->getEvents()->first();

        // maste list has no event attached. So we only need to check if the user is the owner
        if (!$event && $desireList->isMaster() && $user === $desireList->getOwner()) {
            return true;
        }

        dump('List is belonging to event: ' . $event->getId() . '');
        if ($desireList->getEvents()->count() > 1){
            $debug = sprintf(
                "List %s (%s) is shared between %s events:\n%s",
                $desireList->getId(),
                $desireList->getName(),
                $desireList->getEvents()->count(),
                implode('|', $desireList->getEvents()->map(function (Event $event){return $event->getId();})->toArray())
            );

            dump($debug);
        }

        $userIsPartOfEvent = $this->checkUserIsParticipantOfEvent($user, $event);
        if (!$userIsPartOfEvent){
//            return false;
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
        $secretIdents = [];
        $ownerId = -1;
        $eventIsSame = false;
        foreach ($desireListIdents as $ident){
            preg_match('~' . self::REGEX_FOR_SECRET . '~', $ident, $matches);
            if ($matches) {
                $secretIdents[] = $matches[0];
                $ownerId = $matches[1];
                $eventId = $matches[2];
                if (!$eventIsSame){
                    $eventIsSame = (int) $eventId === $event->getId();
                }
            }
        }
        dump('Event: ' . $event->getId() );
        dump('Event found: ' . $eventIsSame );

        $userId = -2;
        foreach ($userIdents as $ident){
            preg_match('~^USER_(\d*)?~', $ident, $matches);
            if ($matches){
                $userId = $matches[1];
            }
        }

        $debug = sprintf(
            "DesirelistIdents:\n%s\n\nUserIdents:\n%s",
            implode('|', $desireListIdents),
            implode('|', $userIdents)
        );
        dump($debug);

        $userIsOwner = $ownerId === $userId;
        $userIsSecret = count(array_intersect($secretIdents, $userIdents)) > 0;
        dump('Is owner: ' . (int) $userIsOwner);
        dump('Is secret: ' . (int) $userIsSecret);
        dump('Is same event: ' . (int) $eventIsSame);

        $shared = array_intersect($desireListIdents, $userIdents);

        if (count($shared) > 0 && ($userIsOwner || ($userIsSecret && $eventIsSame))){
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
