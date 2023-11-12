<?php
declare(strict_types=1);

namespace App\Content\DesireList\Data;

use App\Entity\Desire;
use App\Entity\DesireList;
use App\Entity\User;
use Doctrine\Common\Collections\Collection;

/**
 * @author Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2023 DocCheck Community GmbH
 */
class DesireListData
{
    private User $owner;

    /**
     * @var Desire[]
     */
    private array $desires;

    private array $accessRoles = [];

    public function getOwner(): User
    {
        return $this->owner;
    }

    public function setOwner(User $owner): DesireListData
    {
        $this->owner = $owner;
        return $this;
    }

    public function getDesires(): array
    {
        return $this->desires;
    }

    public function setDesires(array $desires): DesireListData
    {
        $this->desires = $desires;
        return $this;
    }

    public function getAccessRoles(): array
    {
        return $this->accessRoles;
    }

    public function setAccessRoles(array $accessRoles): DesireListData
    {
        $this->accessRoles = $accessRoles;
        return $this;
    }

    public function initFromEntity(DesireList $desireList): DesireListData
    {
        $this->setDesires($desireList->getDesires()->toArray());
        $this->setOwner($desireList->getOwner());
        $this->setAccessRoles($desireList->getAccessRoles());

        return $this;
    }
}
