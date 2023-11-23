<?php
declare(strict_types=1);

namespace App\Content\User;

use App\Content\DesireList\DesireListRepository;
use App\Content\DesireList\DesireListService;
use App\Entity\AccessRole;
use App\Entity\DesireList;
use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @author Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2023 DocCheck Community GmbH
 */
class AccessRoleService
{

    public function __construct(
        private AccessRoleRepository $repository,
        private DesireListRepository $desireListRepository,
        private EntityManagerInterface $entityManager,
        private DesireListService $desireListService)
    {
    }

    public function addAccessRoleToDesireListe(DesireList $desireList, string $ident): void
    {

    }

    public function addRoleToDesireList(DesireList $entity, string $roleIdent){
        $role = $this->repository->findBy(['ident' => $roleIdent]);
        if (count($role) === 1){
            $entity->addAccessRole($role[0]);
        }else{
            $accessRole = new AccessRole();
            $accessRole->setIdent($roleIdent);
            $this->repository->save($accessRole);
            $entity->addAccessRole($accessRole);
        }

        $this->desireListRepository->save($entity);
    }

    public function addRoleToEntity(HasAccessRoleInterface $entity, string $roleIdent){
        $role = $this->repository->findBy(['ident' => $roleIdent]);
        if (count($role) === 1){
            $accessRole = $role[0];
        }else{
            $accessRole = new AccessRole();
            $accessRole->setIdent($roleIdent);
            $this->repository->save($accessRole);
        }

        $entity->addAccessRole($accessRole);
        $a = array_map(
            function ($entity){
                return $entity->getIdent();
            },
            $entity->getAccessRoles()->toArray()
        );
        dump($a);
        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }
}
