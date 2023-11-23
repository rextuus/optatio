<?php
declare(strict_types=1);

namespace App\Content\User;

use App\Entity\AccessRole;

/**
 * @author Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2023 DocCheck Community GmbH
 */
interface HasAccessRoleInterface
{
    public function addAccessRole(AccessRole $accessRole);
}
