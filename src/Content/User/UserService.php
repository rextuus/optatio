<?php
declare(strict_types=1);

namespace App\Content\User;

use App\Content\User\Data\UserRegistrationData;
use App\Entity\Event;
use App\Entity\User;
use App\Entity\UserAccessRoles;

/**
 * @author Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2023 DocCheck Community GmbH
 */
class UserService
{


    public function __construct(
        private UserRepository $repository,
        private UserFactory $userFactory,
        private UserAccessRolesRepository $roleRepository,
    )
    {
    }

    public function addEventOwnerRoleToUser(Event $event, User $user): void
    {
        $eventRoles = ['ROLE_EVENT_' . $event->getId() . '_OWNER'];
        $roles = $user->getUserAccessRoles();

        $user->setRoles(array_merge($user->getRoles(), $eventRoles));
        $this->repository->save($user);
    }

    public function addEventRoleToUser(Event $event, User $participant): void
    {
        $roles = $participant->getUserAccessRoles();

        $roles->addRole('ROLE_EVENT_' . $event->getId() . '_PARTICIPANT');

        $this->roleRepository->save($roles);
    }

    public function removeEventRoleToUser(Event $event, User $participant): void
    {
        $roles = $participant->getUserAccessRoles();
        $roles->removeRole('ROLE_EVENT_' . $event->getId() . '_PARTICIPANT');

        $this->roleRepository->save($roles);
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

    public function storeNewUserByData(UserRegistrationData $userRegistrationData)
    {
        $user = $this->userFactory->createByData($userRegistrationData);

        $this->repository->save($user, true);

        return $user;
    }
}
