<?php
declare(strict_types=1);

namespace App\Content\User\Data;

/**
 * @author Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2023 DocCheck Community GmbH
 */
class UserRegistrationData
{
    private string $userName;

    private string $password;

    private string $email;

    private string $firstName;

    private string $lastName;

    public function getUserName(): string
    {
        return $this->userName;
    }

    public function setUserName(string $userName): UserRegistrationData
    {
        $this->userName = $userName;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): UserRegistrationData
    {
        $this->password = $password;
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): UserRegistrationData
    {
        $this->email = $email;
        return $this;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): UserRegistrationData
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): UserRegistrationData
    {
        $this->lastName = $lastName;
        return $this;
    }
}
