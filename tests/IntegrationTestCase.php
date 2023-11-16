<?php
declare(strict_types=1);

namespace App\Test;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use Nelmio\Alice\Loader\NativeLoader;
use Nelmio\Alice\ObjectSet;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @author Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2023 DocCheck Community GmbH
 */
class IntegrationTestCase extends WebTestCase
{
    /**
     * @var ObjectSet
     */
    private $objectSet;

    protected function setUp(): void
    {
        // boot kernel
        $kernel = self::bootKernel();

        // prepare db
        $doctrineUpdateCommand = sprintf(
            'php bin/console doctrine:schema:update --force > /dev/null'
        );
        exec($doctrineUpdateCommand);
    }

    protected function loadFixtureFiles(array $fixtureFiles): void
    {
        // load fixture file
        $loader = new NativeLoader();
        $this->objectSet = $loader->loadFiles($fixtureFiles);

        $objects = $this->objectSet->getObjects();

        // write fixtures to db
        $container = self::$kernel->getContainer();
        /** @var Registry $repo */
        $repo = $container->get('doctrine');
        $manager = $repo->getManager();
        foreach ($objects as $entity) {
            $manager->persist($entity);
        }
        $manager->flush();
    }

    protected function tearDown(): void
    {
        // clear complete db
        $doctrineDropCommand = sprintf(
            'php bin/console doctrine:database:drop --force --env=test > /dev/null'
        );
        exec($doctrineDropCommand);

        parent::tearDown();
    }

    protected function getService(string $serviceIdent): mixed
    {

        $container = static::getContainer();
        return $container->get($serviceIdent);
    }

    protected function getFixtureEntity(string $class, string $id)
    {
        $container = self::$kernel->getContainer();
        /** @var Registry $repo */
        $repo = $container->get('doctrine');
        $manager = $repo->getManager();
        return $manager->getRepository($class)->find($id);
    }

    protected function getFixtureEntityByIdent(string $ident){
        return $this->objectSet->getObjects()[$ident];
    }


    public function refreshLoadedEntity(&$entity, bool $refreshIfNotContained = false): void
    {
        /** @var EntityManager $entityManager */
        $container = self::$kernel->getContainer();
        /** @var Registry $repo */
        $repo = $container->get('doctrine');
        $entityManager = $repo->getManager();

        if (!$entityManager->contains($entity)) {
            $entity = $entityManager->merge($entity);

            if ($refreshIfNotContained) {
                $entityManager->refresh($entity);
            }
        } else {
            $entityManager->refresh($entity);
        }
    }
}
