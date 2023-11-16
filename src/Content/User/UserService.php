<?php
declare(strict_types=1);

namespace App\Content\User;

use App\Entity\Event;
use App\Entity\User;
use App\Entity\UserAccessRoles;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * @author Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2023 DocCheck Community GmbH
 */
class UserService
{


    public function __construct(private UserRepository $repository, private UserAccessRolesRepository $roleRepository, private TokenStorageInterface $tokenStorage)
    {
    }

    public function addEventOwnerRoleToUser(Event $event, User $user): void
    {
        $eventRoles = ['ROLE_EVENT_' . $event->getId() . '_OWNER'];
        $roles = $user->getUserAccessRoles();

        $user->setRoles(array_merge($user->getRoles(), $eventRoles));
        $this->repository->save($user);
    }

    public function addEventRoleToUser(Event $event, User $user): void
    {
        $eventRoles = ['ROLE_EVENT_' . $event->getId() . '_PARTICIPANT'];
        $user->setRoles(array_merge($user->getRoles(), $eventRoles));

        $token = new UsernamePasswordToken($user, 'main');
        $this->tokenStorage->setToken($token);

        $this->repository->save($user);
    }

    public function removeEventRoleToUser(Event $event, User $participant): void
    {
        $currentlyRoles = $participant->getRoles();
        $key = array_search('ROLE_EVENT_' . $event->getId() . '_PARTICIPANT', $currentlyRoles);
        if ($key !== false) {
            unset($currentlyRoles[$key]);
        }

        $participant->setRoles($currentlyRoles);
        $token = new UsernamePasswordToken($participant, 'main');
        $this->tokenStorage->setToken($token);

        $this->repository->save($participant);
    }

    public function initAccessRolesForUser(User $user): void
    {
        $accessRoles = new UserAccessRoles();
        $accessRoles->addRole('test');
        $accessRoles->setUser($user);
        $user->setUserAccessRoles($accessRoles);

        $this->roleRepository->save($accessRoles);
    }

    /**
     * @return User[]
     */
    public function findAll(): array
    {
        return $this->repository->findAll();
    }

    public function getUser(int $userId): User
    {
        return $this->repository->find($userId);
    }
}
