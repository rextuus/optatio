<?php
declare(strict_types=1);

namespace App\Content\User;

use App\Content\User\Data\UserRegistrationData;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


class UserFactory
{
    public function __construct(private UserPasswordHasherInterface $passwordEncoder)
    {
    }

    public function createByData(UserRegistrationData $userData): User
    {
        $user = $this->createNewUserInstance();
        $this->mapData($user, $userData);

        return $user;
    }

    /**
     * mapData
     *
     * @param User $user
     * @param UserRegistrationData $data
     *
     * @return void
     */
    public function mapData(User $user, UserRegistrationData $data): void
    {
        $user->setEmail($data->getEmail());
        $user->setFirstName($data->getFirstName());
        $user->setLastName($data->getLastName());
        $user->setPassword($this->passwordEncoder->hashPassword($user, $data->getPassword()));
    }

    /**
     * createNewUserInstance
     *
     * @return User
     */
    private function createNewUserInstance(): User
    {
        return new User();
    }
}
