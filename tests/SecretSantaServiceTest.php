<?php
declare(strict_types=1);

namespace App\Test;

use App\Content\Event\EventManager;
use App\Content\SecretSanta\Secret\SecretService;
use App\Content\SecretSanta\SecretSantaEvent\Data\SecretSantaEventJoinData;
use App\Content\SecretSanta\SecretSantaService;
use App\Content\SecretSanta\SecretSantaState;
use App\Content\User\AccessRoleService;
use App\Entity\AccessRole;
use App\Entity\SecretSantaEvent;
use App\Entity\User;

class SecretSantaServiceTest extends IntegrationTestCase
{
    private SecretSantaService $secretSantaService;
    private EventManager $eventManager;
    private AccessRoleService $accessRoleService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loadFixtureFiles(
            [
                __DIR__ . '/fixtures/secret_santa.yml',
            ]
        );
        $this->secretSantaService = $this->getService(SecretSantaService::class);
        $this->eventManager = $this->getService(EventManager::class);
        $this->accessRoleService = $this->getService(AccessRoleService::class);
    }

    public function testTriggerCalculation(): void
    {
        /** @var User $user1 */
        $user1 = $this->getFixtureEntityByIdent('user1');

        /** @var User $user2 */
        $user2 = $this->getFixtureEntityByIdent('user2');

        /** @var User $user3 */
        $user3 = $this->getFixtureEntityByIdent('user3');

        /** @var SecretSantaEvent $ssEvent */
        $ssEvent = $this->getFixtureEntityByIdent('ss_event');
        $this->assertEquals(SecretSantaState::OPEN, $ssEvent->getState());

        $data = new SecretSantaEventJoinData();
        $data->setFirstRound(true);
        $data->setSecondRound(true);
        $this->eventManager->addParticipantToSecretSantaEvent($user1, $ssEvent, $data);
        $this->eventManager->addParticipantToSecretSantaEvent($user2, $ssEvent, $data);
        $this->eventManager->addParticipantToSecretSantaEvent($user3, $ssEvent, $data);

        // EXECUTION
        $this->secretSantaService->triggerCalculation($ssEvent);

        $this->assertEquals(SecretSantaState::PHASE_1, $ssEvent->getState());

        $this->refreshLoadedEntity($ssEvent);

        /** @var SecretService $secretService */
        $secretService = $this->getService(SecretService::class);
        $secrets = $secretService->findBy([]);
        $this->assertCount(6, $secrets);

        // check secret creation
        $this->refreshLoadedEntity($user1);
        $providerSecrets = $user1->getProvidingSecrets()->toArray();
        $this->assertEquals($user1->getId(), $providerSecrets[0]->getProvider()->getId());
        $this->assertEquals($user1->getId(), $providerSecrets[1]->getProvider()->getId());
        $user1Receivers = [$providerSecrets[0]->getReceiver()->getId(), $providerSecrets[1]->getReceiver()->getId()];
        $this->assertContains($user2->getId(), $user1Receivers);
        $this->assertContains($user3->getId(), $user1Receivers);

        $receivingSecrets = $user1->getReceivingSecrets()->toArray();
        $this->assertEquals($user1->getId(), $receivingSecrets[0]->getReceiver()->getId());
        $this->assertEquals($user1->getId(), $receivingSecrets[1]->getReceiver()->getId());
        $user1Providers = [$receivingSecrets[0]->getProvider()->getId(), $receivingSecrets[1]->getProvider()->getId()];
        $this->assertContains($user2->getId(), $user1Providers);
        $this->assertContains($user3->getId(), $user1Providers);

        // check desireLists creation
        $desireList = $user1->getDesireLists()->toArray()[0];
        $this->assertEquals($desireList->getName(), 'Wunschliste - '.$ssEvent->getName());
        $this->assertStringContainsString('Die Liste wird genutzt für die Events:', $desireList->getDescription());
        $this->assertStringContainsString($ssEvent->getFirstRound()->getName(), $desireList->getDescription());
        $this->assertStringContainsString($ssEvent->getSecondRound()->getName(), $desireList->getDescription());

        // check desireLists has both events (round1 and round2) => important for sharing
        $events = $desireList->getEvents()->toArray();

        $this->assertCount(2, $events);
        $this->assertEquals($ssEvent->getFirstRound(), $events[0]);
        $this->assertEquals($ssEvent->getSecondRound(), $events[1]);

        // perform first round picks
        $secret = $this->secretSantaService->performFirstRoundPick($ssEvent, $user1);
        $this->assertEquals($user3, $secret->getReceiver());
        $this->assertEquals(SecretSantaState::PHASE_1, $ssEvent->getState());

        $secret = $this->secretSantaService->performFirstRoundPick($ssEvent, $user2);
        $this->assertEquals($user1, $secret->getReceiver());
        $this->assertEquals(SecretSantaState::PHASE_1, $ssEvent->getState());

        $secret = $this->secretSantaService->performFirstRoundPick($ssEvent, $user3);
        $this->assertEquals($user2, $secret->getReceiver());
        $this->assertEquals(SecretSantaState::PHASE_2, $ssEvent->getState());


        // perform second round picks
        $secret = $this->secretSantaService->performSecondRoundPick($ssEvent, $user1);
        $this->assertEquals($user2, $secret->getReceiver());
        $this->assertEquals(SecretSantaState::PHASE_2, $ssEvent->getState());

        $secret = $this->secretSantaService->performSecondRoundPick($ssEvent, $user3);
        $this->assertEquals($user1, $secret->getReceiver());
        $this->assertEquals(SecretSantaState::PHASE_2, $ssEvent->getState());

        $secret = $this->secretSantaService->performSecondRoundPick($ssEvent, $user2);
        $this->assertEquals($user3, $secret->getReceiver());
        $this->assertEquals(SecretSantaState::RUNNING, $ssEvent->getState());
    }

    public function testTriggerCalculationWillFailDueToExclusion(): void
    {
        /** @var SecretSantaEvent $ssEvent */
        $ssEvent = $this->getFixtureEntityByIdent('ss_event_failing_due_to_exclusion');
        $this->assertEquals(SecretSantaState::OPEN, $ssEvent->getState());

        $this->secretSantaService->triggerCalculation($ssEvent);

        $this->assertEquals(SecretSantaState::OPEN, $ssEvent->getState());
    }

    public function testTriggerCalculationWillWorkEvenWithExclusion(): void
    {
        /** @var User $user1 */
        $user1 = $this->getFixtureEntityByIdent('user1');

        /** @var User $user2 */
        $user2 = $this->getFixtureEntityByIdent('user2');

        /** @var User $user3 */
        $user3 = $this->getFixtureEntityByIdent('user3');

        /** @var User $user4 */
        $user4 = $this->getFixtureEntityByIdent('user4');

        /** @var SecretSantaEvent $ssEvent */
        $ssEvent = $this->getFixtureEntityByIdent('ss_event_working_even_with_exclusion');
        $this->assertEquals(SecretSantaState::OPEN, $ssEvent->getState());

        $data = new SecretSantaEventJoinData();
        $data->setFirstRound(true);
        $data->setSecondRound(true);
        $this->eventManager->addParticipantToSecretSantaEvent($user1, $ssEvent, $data);
        $this->eventManager->addParticipantToSecretSantaEvent($user3, $ssEvent, $data);

        $data->setFirstRound(true);
        $data->setSecondRound(false);
        $this->eventManager->addParticipantToSecretSantaEvent($user2, $ssEvent, $data);

        $data->setFirstRound(false);
        $data->setSecondRound(true);
        $this->eventManager->addParticipantToSecretSantaEvent($user4, $ssEvent, $data);

        $this->secretSantaService->triggerCalculation($ssEvent);

        $this->assertEquals(SecretSantaState::PHASE_1, $ssEvent->getState());

        $this->refreshLoadedEntity($user2);
        $this->refreshLoadedEntity($user4);

        // user2 is only involved in round1 and user4 is only involved in round2
        $desireList = $user2->getDesireLists()->toArray()[0];
        $this->assertEquals($desireList->getName(), 'Wunschliste - '.$ssEvent->getName());
        $this->assertStringContainsString('Die Liste wird genutzt für die Events:', $desireList->getDescription());
        $this->assertStringContainsString($ssEvent->getFirstRound()->getName(), $desireList->getDescription());
        $this->assertStringNotContainsString($ssEvent->getSecondRound()->getName(), $desireList->getDescription());


        $events = $desireList->getEvents()->toArray();

        $this->assertCount(1, $events);
        $this->assertEquals($ssEvent->getFirstRound(), $events[0]);
        //____________________________________________________________________________

        $desireList = $user4->getDesireLists()->toArray()[0];
        $this->assertEquals($desireList->getName(), 'Wunschliste - '.$ssEvent->getName());
        $this->assertStringContainsString('Die Liste wird genutzt für die Events:', $desireList->getDescription());
        $this->assertStringContainsString($ssEvent->getSecondRound()->getName(), $desireList->getDescription());
        $this->assertStringNotContainsString($ssEvent->getFirstRound()->getName(), $desireList->getDescription());

        $events = $desireList->getEvents()->toArray();

        $this->assertCount(1, $events);
        $this->assertEquals($ssEvent->getSecondRound(), $events[0]);
    }

    public function testTriggerCalculationWillFailDueToBidirectionalExclusion(): void
    {
        /** @var User $user1 */
        $user1 = $this->getFixtureEntityByIdent('user1');

        /** @var User $user2 */
        $user2 = $this->getFixtureEntityByIdent('user2');

        /** @var User $user3 */
        $user3 = $this->getFixtureEntityByIdent('user3');

        /** @var User $user4 */
        $user4 = $this->getFixtureEntityByIdent('user4');

        /** @var SecretSantaEvent $ssEvent */
        $ssEvent = $this->getFixtureEntityByIdent('ss_event_failing_due_to_exclusion_bidirectional');
        $this->assertEquals(SecretSantaState::OPEN, $ssEvent->getState());

        $data = new SecretSantaEventJoinData();
        $data->setFirstRound(true);
        $data->setSecondRound(true);
        $this->eventManager->addParticipantToSecretSantaEvent($user1, $ssEvent, $data);
        $this->eventManager->addParticipantToSecretSantaEvent($user3, $ssEvent, $data);

        $data->setFirstRound(true);
        $data->setSecondRound(false);
        $this->eventManager->addParticipantToSecretSantaEvent($user2, $ssEvent, $data);

        $data->setFirstRound(false);
        $data->setSecondRound(true);
        $this->eventManager->addParticipantToSecretSantaEvent($user4, $ssEvent, $data);

        $this->secretSantaService->triggerCalculation($ssEvent);

        $this->assertEquals(SecretSantaState::OPEN, $ssEvent->getState());
    }


    public function testTriggerCalculationSingleSecretSantaEvent(): void
    {
        /** @var User $user1 */
        $user1 = $this->getFixtureEntityByIdent('user1');

        /** @var User $user2 */
        $user2 = $this->getFixtureEntityByIdent('user2');

        /** @var User $user3 */
        $user3 = $this->getFixtureEntityByIdent('user3');

        /** @var User $user4 */
        $user4 = $this->getFixtureEntityByIdent('user4');

        /** @var SecretSantaEvent $ssEvent */
        $ssEvent = $this->getFixtureEntityByIdent('ss_event_single_round');
        $this->assertEquals(SecretSantaState::OPEN, $ssEvent->getState());

        $data = new SecretSantaEventJoinData();
        $data->setFirstRound(true);
        $data->setSecondRound(false);
        $this->eventManager->addParticipantToSecretSantaEvent($user1, $ssEvent, $data);
        $this->eventManager->addParticipantToSecretSantaEvent($user2, $ssEvent, $data);
        $this->eventManager->addParticipantToSecretSantaEvent($user3, $ssEvent, $data);
        $this->eventManager->addParticipantToSecretSantaEvent($user4, $ssEvent, $data);

        $this->secretSantaService->addGodfather($user4, $ssEvent);

        // EXECUTION
        $this->secretSantaService->triggerCalculation($ssEvent);

        $this->assertEquals(SecretSantaState::PHASE_1, $ssEvent->getState());

        $this->refreshLoadedEntity($ssEvent);

        /** @var SecretService $secretService */
        $secretService = $this->getService(SecretService::class);
        $secrets = $secretService->findBy([]);
        $this->assertCount(3, $secrets);

        // check secret creation
        $this->refreshLoadedEntity($user1);
        $providerSecrets = $user1->getProvidingSecrets()->toArray();
        $this->assertEquals($user1->getId(), $providerSecrets[0]->getProvider()->getId());
        $user1Receivers = [$providerSecrets[0]->getReceiver()->getId()];
        $this->assertContains($user3->getId(), $user1Receivers);

        $receivingSecrets = $user1->getReceivingSecrets()->toArray();
        $this->assertEquals($user1->getId(), $receivingSecrets[0]->getReceiver()->getId());
        $user1Providers = [$receivingSecrets[0]->getProvider()->getId()];
        $this->assertContains($user2->getId(), $user1Providers);

        // check desireLists creation
        $desireList = $user1->getDesireLists()->toArray()[0];
        $this->assertEquals($desireList->getName(), 'Wunschliste - '.$ssEvent->getName());
        $this->assertStringContainsString('Die Liste wird genutzt für die Events:', $desireList->getDescription());
        $this->assertStringContainsString($ssEvent->getFirstRound()->getName(), $desireList->getDescription());

        // check desireLists has both events (round1 and round2) => important for sharing
        $events = $desireList->getEvents()->toArray();

        $this->assertCount(1, $events);
        $this->assertEquals($ssEvent->getFirstRound(), $events[0]);

        // check user4 has no desireList
        $this->assertCount(0, $user4->getDesireLists()->toArray());
        // check user4 has access to all desireLists
        $user4AccessRoles = array_map(
            fn (AccessRole $role) => $role->getIdent(),
            $user4->getAccessRoles()->toArray()
        );

        $expectedRoles = [
            'ROLE_SECRET_FOR_USER_1_EVENT_5',
            'ROLE_SECRET_FOR_USER_2_EVENT_5',
            'ROLE_SECRET_FOR_USER_3_EVENT_5',
            'ROLE_SECRET_FOR_USER_4_EVENT_5',
        ];

        foreach ($expectedRoles as $expectedRole) {
            $this->assertContains($expectedRole, $user4AccessRoles);
        }

        //check each user can access godfatherList
        $users = [$user1, $user2, $user3];
        foreach ($users as $user) {
            $accessRolesOfUser = array_map(
                fn (AccessRole $role) => $role->getIdent(),
                $user->getAccessRoles()->toArray()
            );
            $this->assertContains('ROLE_SECRET_FOR_USER_4_EVENT_5', $accessRolesOfUser);
        }

        // perform first round picks
        $secret = $this->secretSantaService->performFirstRoundPick($ssEvent, $user1);
        $this->assertEquals($user3, $secret->getReceiver());
        $this->assertEquals(SecretSantaState::PHASE_1, $ssEvent->getState());

        $secret = $this->secretSantaService->performFirstRoundPick($ssEvent, $user2);
        $this->assertEquals($user1, $secret->getReceiver());
        $this->assertEquals(SecretSantaState::PHASE_1, $ssEvent->getState());

        $secret = $this->secretSantaService->performFirstRoundPick($ssEvent, $user3);
        $this->assertEquals($user2, $secret->getReceiver());
        $this->assertEquals(SecretSantaState::RUNNING, $ssEvent->getState());
    }

    public function testCheckListAccessRights(): void
    {
        /** @var User $user1 */
        $user1 = $this->getFixtureEntityByIdent('user1');

        /** @var User $user2 */
        $user2 = $this->getFixtureEntityByIdent('user2');

        /** @var User $user3 */
        $user3 = $this->getFixtureEntityByIdent('user3');

        /** @var SecretSantaEvent $ssEvent */
        $ssEvent = $this->getFixtureEntityByIdent('ss_event');
        $this->assertEquals(SecretSantaState::OPEN, $ssEvent->getState());

        $data = new SecretSantaEventJoinData();
        $data->setFirstRound(true);
        $data->setSecondRound(true);
        $this->eventManager->addParticipantToSecretSantaEvent($user1, $ssEvent, $data);
        $this->eventManager->addParticipantToSecretSantaEvent($user2, $ssEvent, $data);
        $this->eventManager->addParticipantToSecretSantaEvent($user3, $ssEvent, $data);

        // EXECUTION
        $this->secretSantaService->triggerCalculation($ssEvent);

        $this->assertEquals(SecretSantaState::PHASE_1, $ssEvent->getState());

        $this->refreshLoadedEntity($ssEvent);

        /** @var SecretService $secretService */
        $secretService = $this->getService(SecretService::class);
        $secrets = $secretService->findBy([]);
        $this->assertCount(6, $secrets);

        // check secret creation
        $this->refreshLoadedEntity($user1);
        $this->refreshLoadedEntity($user2);
        $this->refreshLoadedEntity($user3);
        $providerSecrets = $user1->getProvidingSecrets()->toArray();
        $this->assertEquals($user1->getId(), $providerSecrets[0]->getProvider()->getId());
        $this->assertEquals($user1->getId(), $providerSecrets[1]->getProvider()->getId());
        $user1Receivers = [$providerSecrets[0]->getReceiver()->getId(), $providerSecrets[1]->getReceiver()->getId()];
        $this->assertContains($user2->getId(), $user1Receivers);
        $this->assertContains($user3->getId(), $user1Receivers);

        $receivingSecrets = $user1->getReceivingSecrets()->toArray();
        $this->assertEquals($user1->getId(), $receivingSecrets[0]->getReceiver()->getId());
        $this->assertEquals($user1->getId(), $receivingSecrets[1]->getReceiver()->getId());
        $user1Providers = [$receivingSecrets[0]->getProvider()->getId(), $receivingSecrets[1]->getProvider()->getId()];
        $this->assertContains($user2->getId(), $user1Providers);
        $this->assertContains($user3->getId(), $user1Providers);

        // check desireLists creation
        $desireList = $user1->getDesireLists()->toArray()[0];
        $this->assertEquals($desireList->getName(), 'Wunschliste - '.$ssEvent->getName());
        $this->assertStringContainsString('Die Liste wird genutzt für die Events:', $desireList->getDescription());
        $this->assertStringContainsString($ssEvent->getFirstRound()->getName(), $desireList->getDescription());
        $this->assertStringContainsString($ssEvent->getSecondRound()->getName(), $desireList->getDescription());

        // check desireLists has both events (round1 and round2) => important for sharing
        $events = $desireList->getEvents()->toArray();

        $this->assertCount(2, $events);
        $this->assertEquals($ssEvent->getFirstRound(), $events[0]);
        $this->assertEquals($ssEvent->getSecondRound(), $events[1]);




        $event1 = $ssEvent->getFirstRound();
        $event2 = $ssEvent->getSecondRound();
        // user2 and user3 should both have access to list of user1. But only by one of the events
        $resultRound1 = $this->accessRoleService->checkDesireListAccess($user2, $desireList, $event1);
        $resultRound2 = $this->accessRoleService->checkDesireListAccess($user2, $desireList, $event2);
        $this->assertNotEquals($resultRound1, $resultRound2);

        $resultRound1 = $this->accessRoleService->checkDesireListAccess($user3, $desireList, $event1);
        $resultRound2 = $this->accessRoleService->checkDesireListAccess($user3, $desireList, $event2);
        $this->assertNotEquals($resultRound1, $resultRound2);
    }
}
