<?php
declare(strict_types=1);

namespace App\Content\User;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Events;


#[AsDoctrineListener(event: Events::postPersist, priority: 500, connection: 'default')]
class UserAddAccessRoleListener
{

    public function __construct(private UserService $userService)
    {

    }

    public function postPersist(PostPersistEventArgs $event): void
    {
        $user = $event->getObject();
        if ($user instanceof User){
            $this->userService->initAccessRolesForUser($user);
        }
    }
}
