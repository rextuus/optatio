<?php
declare(strict_types=1);

namespace App\Test;

use App\Content\DesireList\DesireListService;
use App\Content\Event\EventManager;
use App\Content\SecretSanta\SecretSantaEvent\Data\SecretSantaEventJoinData;
use App\Content\SecretSanta\SecretSantaState;
use App\Entity\AccessRole;
use App\Entity\SecretSantaEvent;
use App\Entity\User;

/**
 * @author Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2023 DocCheck Community GmbH
 */
class EventManagerTest extends IntegrationTestCase
{
    private EventManager $eventManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loadFixtureFiles(
            [
                __DIR__ . '/fixtures/secret_santa.yml',
            ]
        );
        $this->eventManager = $this->getService(EventManager::class);
    }

    public function testTriggerCalculation(): void
    {
        /** @var User $user5 */
        $user5 = $this->getFixtureEntityByIdent('user5');

        /** @var SecretSantaEvent $ssEvent */
        $ssEvent = $this->getFixtureEntityByIdent('ss_event');
        $this->assertEquals(SecretSantaState::OPEN, $ssEvent->getState());

        $data = new SecretSantaEventJoinData();
        $data->setFirstRound(true);
        $data->setSecondRound(true);
        // EXECUTION
        $this->eventManager->addParticipantToSecretSantaEvent($user5, $ssEvent, $data);

        dump(
            $user5->getAccessRoles()->map(
                function (AccessRole $accessRole) {
                    return $accessRole->getIdent();
                })
        );

        /** @var DesireListService $desireListService */
        $desireListService = $this->getService(DesireListService::class);
        $lists = $desireListService->findBy(['owner' => $user5]);
        $list = $lists[0];
        dump(
            $list->getAccessRoles()->map(
                function (AccessRole $accessRole) {
                    return $accessRole->getIdent();
                })
        );
    }
}
