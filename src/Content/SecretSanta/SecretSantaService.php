<?php
declare(strict_types=1);

namespace App\Content\SecretSanta;

use App\Content\Desire\DesireService;
use App\Content\DesireList\Data\DesireListData;
use App\Content\DesireList\DesireListService;
use App\Content\SecretSanta\Calculation\PotentialSecret;
use App\Content\SecretSanta\Calculation\SecretCalculator;
use App\Content\SecretSanta\Secret\Data\SecretData;
use App\Content\SecretSanta\Secret\SecretService;
use App\Content\SecretSanta\SecretSantaEvent\Data\SecretSantaEventData;
use App\Content\SecretSanta\SecretSantaEvent\Data\SecretSantaEventJoinData;
use App\Content\SecretSanta\SecretSantaEvent\SecretSantaEventService;
use App\Content\User\AccessRoleService;
use App\Content\User\UserService;
use App\Entity\DesireList;
use App\Entity\Event;
use App\Entity\Secret;
use App\Entity\SecretSantaEvent;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;


class SecretSantaService
{
    public function __construct(
        readonly private SecretCalculator        $secretCalculator,
        readonly private SecretSantaEventService $secretSantaEventService,
        readonly private UserService             $userService,
        readonly private SecretService           $secretService,
        readonly private DesireListService       $desireListService,
        readonly private DesireService           $desireService,
        readonly private AccessRoleService       $accessRoleService,
        readonly private EntityManagerInterface  $entityManager,
    )
    {
    }

    public function testCalculation(SecretSantaEvent $event): Calculation\CalculationResult
    {
        return $this->secretCalculator->testCalculateSecrets(
            $event
        );
    }

    public function triggerCalculation(SecretSantaEvent $event): void
    {
        $calculationResult = $this->secretCalculator->testCalculateSecrets(
            $event
        );

        if ($calculationResult->isSuccess() && $calculationResult->checkIntegrity()) {
            // secrets round1
            foreach ($calculationResult->getRound1() as $potentialSecret) {
                $secretData = $this->prepareSecretData($event, $event->getFirstRound(), $potentialSecret);
                $this->secretService->createByData($secretData);

                $receiver = $this->userService->getUser($potentialSecret->getReceiver());
                $provider = $this->userService->getUser($potentialSecret->getProvider());
                // give user role to access only list of secret TODO alternative: only after picking to avoid user can see his secret by testing desireLists
                $this->accessRoleService->addSecretRoleToProvider($provider, $receiver, $event->getFirstRound());
            }

            // secrets round2
            if ($event->isIsDoubleRound()){
                foreach ($calculationResult->getRound2() as $potentialSecret) {
                    $secretData = $this->prepareSecretData($event, $event->getSecondRound(), $potentialSecret);
                    $this->secretService->createByData($secretData);

                    $receiver = $this->userService->getUser($potentialSecret->getReceiver());
                    $provider = $this->userService->getUser($potentialSecret->getProvider());
                    // give user role to access only list of secret TODO alternative: only after picking to avoid user can see his secret by testing desireLists
                    $this->accessRoleService->addSecretRoleToProvider($provider, $receiver, $event->getSecondRound());
                }
            }

            // desireLists round1
            foreach ($event->getFirstRound()->getParticipants() as $participant) {
                // add accessRole to desireList and provider
                $this->addSecretAccessRoleToDesireListOfEvent($participant, $event->getFirstRound());
            }

            // desireLists round2
            if ($event->isIsDoubleRound()) {
                foreach ($event->getSecondRound()->getParticipants() as $participant) {
                    $desireLists = $this->desireListService->findByUserAndEvent($participant, $event->getFirstRound());
                    if (count($desireLists) > 0) {
                        $desireList = $desireLists[0];

                        $desireListData = (new DesireListData())->initFromEntity($desireList);
                        $desireListData->setEvents(
                            array_merge(
                                $desireListData->getEvents(),
                                [$event->getSecondRound()]
                            )
                        );
                        $this->desireListService->update($desireList, $desireListData);

                        $this->addSecretAccessRoleToDesireListOfEvent($participant, $event->getSecondRound());
                    } else {
                        // What happens if participant has no desireList for round1 cause he was not in round1?

                        $this->addSecretAccessRoleToDesireListOfEvent($participant, $event->getSecondRound());
                    }
                }
            }

            // give godFathers access to every DesireList in round
            foreach ($event->getGodFathers() as $godFather){
                foreach ($event->getOverallParticipants() as $participant){
                    // give godfather access to desireList of each participant
                    $this->accessRoleService->addSecretRoleToProvider($godFather, $participant, $event->getFirstRound());
                    if ($event->isIsDoubleRound()){
                        $this->accessRoleService->addSecretRoleToProvider($godFather, $participant, $event->getSecondRound());
                    }

                    // give participant access to godfathers desireList
                    $this->accessRoleService->addSecretRoleToProvider($participant, $godFather, $event->getFirstRound());
                    if ($event->isIsDoubleRound()){
                        $this->accessRoleService->addSecretRoleToProvider($participant, $godFather, $event->getSecondRound());
                    }
                }
            }


            // set ss state to round1 => user of round one can "pick" their already calculated secret
            $ssEventData = (new SecretSantaEventData())->initFromEntity($event);
            $ssEventData->setSecretSantaState(SecretSantaState::PHASE_1);
            $this->secretSantaEventService->update($event, $ssEventData);
        }
    }

    public function performFirstRoundPick(SecretSantaEvent $event, User $participant): Secret
    {
        $nextState = SecretSantaState::PHASE_2;
        if (!$event->isIsDoubleRound()){
            $nextState = SecretSantaState::RUNNING;
        }

        return $this->pickSecretByEventAndUser($event, $event->getFirstRound(), $participant, $nextState);
    }

    public function performSecondRoundPick(SecretSantaEvent $event, User $participant): Secret
    {
        return $this->pickSecretByEventAndUser($event, $event->getSecondRound(), $participant, SecretSantaState::RUNNING);
    }

    public function userHasAlreadyPickedSecretForEvent(Event $event, UserInterface $participant): bool
    {
        $secrets = $this->secretService->findBy(['event' => $event, 'provider' => $participant]);

        if (count($secrets) !== 1) {
            throw new \Exception('Secret for event and user is not unique!');
        }

        $secret = $secrets[0];

        return $secret->isRetrieved();
    }

    private function pickSecretByEventAndUser(
        SecretSantaEvent $secretEvent,
        Event            $event,
        User             $participant,
        SecretSantaState $nextState
    ): Secret
    {
        $secrets = $this->secretService->findBy(['event' => $event, 'provider' => $participant]);

        if (count($secrets) !== 1) {
            throw new \Exception('Secret for event and user is not unique! Found' . count($secrets));
        }

        $secret = $secrets[0];

        // set retrieved
        $secretData = (new SecretData())->initFromEntity($secret);
        $secretData->setRetrieved(true);
        $this->secretService->update($secret, $secretData);

        // check round1 is completed
        $nonRetrieved = $this->secretService->findBy(['event' => $event, 'retrieved' => false]);
        if (count($nonRetrieved) === 0) {
            $ssEventData = (new SecretSantaEventData())->initFromEntity($secretEvent);
            $ssEventData->setSecretSantaState($nextState);
            $this->secretSantaEventService->update($secretEvent, $ssEventData);
        }

        return $secret;
    }

    private function prepareSecretData(SecretSantaEvent $secretEvent, Event $event, PotentialSecret $potentialSecret): SecretData
    {
        $secretData = new SecretData();
        $secretData->setSecretSantaEvent($secretEvent);
        $secretData->setEvent($event);
        $provider = $this->userService->getUser($potentialSecret->getProvider());
        $secretData->setProvider($provider);
        $receiver = $this->userService->getUser($potentialSecret->getReceiver());
        $secretData->setReceiver($receiver);
        $secretData->setRetrieved(false);

        return $secretData;
    }

    public function addSecretAccessRoleToDesireListOfEvent(User $participant, Event $event): void
    {
        $desireLists = $this->desireListService->findByUserAndEvent($participant, $event);

        if (count($desireLists) !== 1) {
            throw new \Exception('User has no desireList for event');
        }

        $desireList = $desireLists[0];

        $this->accessRoleService->addSecretRoleToDesireList($desireList, $participant, $event);
    }

    /**
     * @param SecretSantaEvent $event
     * @param User $participant
     * @return Secret[]
     */
    public function getSecretsForUser(SecretSantaEvent $event, User $participant)
    {
        $firsts = $this->secretService->findBy(['event' => $event->getFirstRound(), 'provider' => $participant]);
        $first = null;
        if (count($firsts)) {
            $first = $firsts[0];
        }
        $seconds = $this->secretService->findBy(['event' => $event->getSecondRound(), 'provider' => $participant]);
        $second = null;
        if (count($seconds)) {
            $second = $seconds[0];
        }

        return ['first' => $first, 'second' => $second];
    }

    public function getSecretStatisticForEvent(SecretSantaEvent $event): SecretSantaEventStatistic
    {
        $firstRoundId = $event->getFirstRound()->getId();
        $secondRoundId = -1;
        if ($event->isIsDoubleRound()){
            $secondRoundId = $event->getSecondRound()->getId();
        }
        $result = $this->secretService->getStatistic($event);

        $statistic = new SecretSantaEventStatistic();
        foreach ($result as $entry) {
            if ($entry['eventId'] === $firstRoundId && !$entry['retrievedState']) {
                $statistic->setFirstRoundNonRetrieved($entry['amount']);
            }
            if ($entry['eventId'] === $firstRoundId && $entry['retrievedState']) {
                $statistic->setFirstRoundRetrieved($entry['amount']);
            }

            if ($entry['eventId'] === $secondRoundId && !$entry['retrievedState']) {
                $statistic->setSecondRoundNonRetrieved($entry['amount']);
            }
            if ($entry['eventId'] === $secondRoundId && $entry['retrievedState']) {
                $statistic->setSecondRoundRetrieved($entry['amount']);
            }
        }

        $userIdsSecondRound = [];
        if ($event->isIsDoubleRound()) {
            $userIdsSecondRound = $event->getSecondRound()->getParticipants()->map(
                function (User $user) {
                    return $user->getId();
                }
            )->toArray();
        }

        $participants = array_unique(array_merge(
            $event->getFirstRound()->getParticipants()->map(
                function (User $user) {
                    return $user->getId();
                }
            )->toArray(),
            $userIdsSecondRound
        ));

        $desires1 = $this->desireService->getAllDesiresForSecretSantaEvent($event);

        $excludesUsers = [];
        $desireCount = 0;
        $reservations = 0;
        foreach ($desires1 as $desire){
            $excludesUsers[] = $desire['user'];
            if($desire['reserved']){
                $reservations = $reservations + $desire['desires'];
            }
            $desireCount = $desireCount + $desire['desires'];
        }

        if ($event->isIsDoubleRound()) {
            $desires2 = $this->desireService->getAllDesiresForSecretSantaEvent($event, false);
            foreach ($desires2 as $desire){
                if (in_array($desire['user'], $excludesUsers)){
                    continue;
                }

                $excludesUsers[] = $desire['user'];
                if($desire['reserved']){
                    $reservations = $reservations + $desire['desires'];
                }
                $desireCount = $desireCount + $desire['desires'];
            }
        }
        $excludesUsers = array_unique($excludesUsers);

        $noDesires = count(array_diff($participants, $excludesUsers));
        $statistic->setUserWithoutDesires($noDesires);
        $statistic->setDesiresTotal($desireCount);
        $statistic->setDesiresReserved($reservations);

        return $statistic;
    }

    public function addGodfather(User $user, SecretSantaEvent $event): void
    {
//        $user = $this->userService->getUser($userId);
        $event->addGodfather($user);

        $this->entityManager->persist($event);
        $this->entityManager->flush();
    }
}
