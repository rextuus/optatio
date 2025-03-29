<?php

namespace App\DataFixtures;

use App\Content\Desire\Data\DesireData;
use App\Content\Desire\DesireManager;
use App\Content\Desire\DesireState;
use App\Content\Event\EventManager;
use App\Content\Event\EventType;
use App\Content\SecretSanta\SecretSantaEvent\Data\SecretSantaEventCreateData;
use App\Content\SecretSanta\SecretSantaEvent\Data\SecretSantaEventData;
use App\Content\SecretSanta\SecretSantaEvent\Data\SecretSantaEventJoinData;
use App\Content\SecretSanta\SecretSantaEvent\SecretSantaEventService;
use App\Content\SecretSanta\SecretSantaService;
use App\Content\SecretSanta\SecretSantaState;
use App\Content\User\UserService;
use App\Entity\Desire;
use App\Entity\DesireList;
use App\Entity\Event;
use App\Entity\Priority;
use App\Entity\SecretSantaEvent;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $hasher,
        private DesireManager $desireManager,
        private EventManager $eventManager,
        private SecretSantaEventService $secretSantaEventService,
        private SecretSantaService $secretSantaService,
        private UserService $userService,
    )
    {
    }

    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);
        $users = [];
        for ($i = 0; $i < 5; $i++) {
            $user = new User();
            $user->setEmail('user' . $i . '@mail.com ');
            $user->setFirstName('Peter' . $i);
            $user->setPassword('$2y$13$tpF5lSTwDUasuUJmTm1AheHE1JQ5sy32HAVOJUMIr8DbliRe0KXWW'); // 123Katzen
            $user->setLastName('Pan' . $i);
            $user->setIsVerified(true);
            $manager->persist($user);
            $users[] = $user;
        }

//        $round1 = new Event();
//        $round1->setName('24.');
//        $round1->setCreator($users[0]);
//        $round1->setEventType(EventType::SECRET_SANTA);
//        $round1->setOpenToJoin(true);
//        $manager->persist($round1);
//
//
//        $round2 = new Event();
//        $round2->setName('25.');
//        $round2->setCreator($users[0]);
//        $round2->setEventType(EventType::SECRET_SANTA);
//        $round2->setOpenToJoin(true);
//        $manager->persist($round2);
//
//
//        $ssEvent = new SecretSantaEvent();
//        $ssEvent->setName('Weihnachten');
//        $ssEvent->setCreator($users[0]);
//        $ssEvent->setFirstRound($round1);
//        $ssEvent->setSecondRound($round2);
//        $ssEvent->setState(SecretSantaState::OPEN);
//        $manager->persist($ssEvent);
//
//        $desireList = new DesireList();
//        $desireList->setDescription('Liste für '.$ssEvent->getName());
//        $desireList->setName('Liste für '.$ssEvent->getName());
//        $desireList->setOwner($users[0]);
//        $desireList->addEvent($round2);
//
//        $manager->persist($desireList);
//        $manager->flush();
//
//        $desireList2 = new DesireList();
//        $desireList2->setDescription('Liste für '.$ssEvent->getName());
//        $desireList2->setName('Liste für '.$ssEvent->getName());
//        $desireList2->setOwner($users[0]);
//        $manager->persist($desireList2);
//        $manager->flush();
//
//
//        $desires = [];
//        for ($i = 0; $i < 5; $i++) {
//            $priority = new Desire();
//            $priority->setState(DesireState::FREE);
//            $priority->setPriority(1000);
//            $priority->setExactly(1);
//            $priority->setExactly(false);
//            $priority->setExclusive(false);
//            $priority->setName('Desire'.$i);
//            $priority->setListed(true);
//            $priority->setOwner($users[0]);
//            $priority->setDescription('Desire'.$i.' with a useless description which is only for displaying purposes');
//
//            $desireList->addDesire($priority);
//            $desireList2->addDesire($priority);
//
//            $manager->persist($priority);
//
//            $desires[] = $priority;
//        }
//
//        $priorities = [];
//        for ($i = 0; $i < 5; $i++) {
//            $priority = new Priority();
//            $priority->setDesire($desires[$i]);
//            $priority->setDesireList($desireList);
//            $priority->setValue($i);
//
//            $manager->persist($priority);
//
//            $priorities[] = $priority;
//        }
//
//        $v = 0;
//        for ($i = 4; $i >= 0; $i--) {
//            $priority = new Priority();
//            $priority->setDesire($desires[$i]);
//            $priority->setDesireList($desireList2);
//            $priority->setValue($v);
//            $v++;
//
//            $manager->persist($priority);
//
//            $priorities[] = $priority;
//        }

        $manager->flush();

//        $this->createSecretSantaEvent();
//
//        $user = $this->userService->findAll();
//        $ssEventData = new SecretSantaEventCreateData();
//        $ssEventData->setName('Oster Weihnachten');
//        $ssEventData->setFirstRoundName('Heilig Abend Oster');
//        $ssEventData->setSecondRoundName(null);
//
//        $ssEvent = $this->eventManager->initSecretSantaEvent($ssEventData, $user[0]);
//
//        $data = new SecretSantaEventJoinData();
//        $data->setFirstRound(true);
//        $data->setSecondRound(false);
//        $this->eventManager->addParticipantToSecretSantaEvent($user[0], $ssEvent, $data);
//
//        $data = new SecretSantaEventJoinData();
//        $data->setFirstRound(true);
//        $data->setSecondRound(false);
//        $this->eventManager->addParticipantToSecretSantaEvent($user[1], $ssEvent, $data);
//
//        $data = new SecretSantaEventJoinData();
//        $data->setFirstRound(true);
//        $data->setSecondRound(false);
//        $this->eventManager->addParticipantToSecretSantaEvent($user[3], $ssEvent, $data);
//
//        $data = new SecretSantaEventJoinData();
//        $data->setFirstRound(true);
//        $data->setSecondRound(false);
//        $this->eventManager->addParticipantToSecretSantaEvent($user[4], $ssEvent, $data);
//
//        $this->secretSantaService->addGodfather($user[4], $ssEvent);
//
//        $this->secretSantaService->triggerCalculation($ssEvent);

    }

    public function createSecretSantaEvent(){
        $user = $this->userService->findAll();
        $ssEventData = new SecretSantaEventCreateData();
        $ssEventData->setName('New Weihnachten');
        $ssEventData->setFirstRoundName('Heilig Abend');
        $ssEventData->setSecondRoundName('Weihnachten');

        $ssEvent = $this->eventManager->initSecretSantaEvent($ssEventData, $user[0]);

        $data = new SecretSantaEventJoinData();
        $data->setFirstRound(true);
        $data->setSecondRound(true);
        $this->eventManager->addParticipantToSecretSantaEvent($user[0], $ssEvent, $data);

        $data = new SecretSantaEventJoinData();
        $data->setFirstRound(false);
        $data->setSecondRound(true);
        $this->eventManager->addParticipantToSecretSantaEvent($user[1], $ssEvent, $data);

        $data = new SecretSantaEventJoinData();
        $data->setFirstRound(true);
        $data->setSecondRound(false);
        $this->eventManager->addParticipantToSecretSantaEvent($user[2], $ssEvent, $data);

        $this->secretSantaService->triggerCalculation($ssEvent);
    }
}
