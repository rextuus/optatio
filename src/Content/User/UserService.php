<?php
declare(strict_types=1);

namespace App\Content\User;

use App\Content\User\Data\UserRegistrationData;
use App\Entity\AccessRole;
use App\Entity\Event;
use App\Entity\User;
use App\Entity\UserAccessRoles;


class UserService
{


    public function __construct(
        private UserRepository $repository,
        private UserFactory $userFactory,
        private AccessRoleRepository $roleRepository,
    )
    {
    }

    public function addEventOwnerRoleToUser(Event $event, User $user): void
    {
        $this->addRole($user, 'ROLE_EVENT_' . $event->getId() . '_OWNER');
    }

    public function addEventRoleToUser(Event $event, User $participant): void
    {
        $this->addRole($participant, 'ROLE_EVENT_' . $event->getId() . '_PARTICIPANT');
    }

    public function removeEventRoleToUser(Event $event, User $participant): void
    {
//        $roles = $participant->getUserAccessRoles();
//        $roles->removeRole('ROLE_EVENT_' . $event->getId() . '_PARTICIPANT');
//
//        $this->roleRepository->save($roles);
    }

    public function initAccessRolesForUser(User $user): void
    {
        $roleIdent = 'USER_'.$user->getId();
        $this->addRole($user, $roleIdent);
    }

    public function addRole(User $user, string $roleIdent){
        $role = $this->roleRepository->findBy(['ident' => $roleIdent]);
        if (count($role) === 1){
            $user->addAccessRole($role[0]);
        }else{
            $accessRole = new AccessRole();
            $accessRole->setIdent($roleIdent);
            $this->roleRepository->save($accessRole);
            $user->addAccessRole($accessRole);
        }

        $this->repository->save($user);
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

//    public function addRolesToUser(User $participant, array $eventRoles)
//    {
//        foreach ($eventRoles as $role){
//            $participant->getUserAccessRoles()->addRole($role);
//        }
//        $this->accessRoleRepository->save($participant);
//    }
}
