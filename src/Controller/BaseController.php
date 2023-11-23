<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @author Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2023 DocCheck Community GmbH
 */
class BaseController extends AbstractController
{
    protected function getLoggedInUser(): ?User
    {
        $user = parent::getUser();
        if (!$user instanceof User) {
            throw new \Exception('Invalid user class given: ' . get_class($user));
        }
        return $user;
    }
}
