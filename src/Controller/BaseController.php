<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\User\UserInterface;

class BaseController extends AbstractController
{
    /**
     * @throws Exception
     */
    protected function getLoggedInUser(): User
    {
        $user = parent::getUser();
        if (!$user instanceof User) {
            throw new Exception('Invalid user class given: ' . get_class($user));
        }
        return $user;
    }
}
