<?php
declare(strict_types=1);

namespace App\Content\SecretSanta;

use App\Content\DesireList\Data\DesireListData;
use App\Content\DesireList\DesireListService;
use App\Content\SecretSanta\Calculation\PotentialSecret;
use App\Content\SecretSanta\Calculation\SecretCalculator;
use App\Content\SecretSanta\Secret\Data\SecretData;
use App\Content\SecretSanta\Secret\SecretService;
use App\Content\SecretSanta\SecretSantaEvent\Data\SecretSantaEventData;
use App\Content\SecretSanta\SecretSantaEvent\SecretSantaEventService;
use App\Content\User\UserService;
use App\Entity\DesireList;
use App\Entity\Event;
use App\Entity\Secret;
use App\Entity\SecretSantaEvent;
use App\Entity\User;

/**
 * @author Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2023 DocCheck Community GmbH
 */
class SecretSantaService
{

    public function __construct(
        readonly private SecretCalculator        $secretCalculator,
        readonly private SecretSantaEventService $secretSantaEventService,
        readonly private UserService             $userService,
        readonly private SecretService           $secretService,
        readonly private DesireListService       $desireListService,
    )
    {
    }

    public function triggerCalculation(SecretSantaEvent $event): void
    {
        $calculationResult = $this->secretCalculator->testCalculateSecrets(
            $event->getFirstRound()->getParticipants()->toArray(),
            $event->getSecondRound()->getParticipants()->toArray(),
            $event->getExclusions()->toArray()
        );

        if ($calculationResult->isSuccess() && $calculationResult->checkIntegrity()) {
            // secrets round1
            foreach ($calculationResult->getRound1() as $potentialSecret) {
                $secretData = $this->prepareSecretData($event, $event->getFirstRound(), $potentialSecret);
                $this->secretService->createByData($secretData);
            }

            // secrets round2
            foreach ($calculationResult->getRound2() as $potentialSecret) {
                $secretData = $this->prepareSecretData($event, $event->getSecondRound(), $potentialSecret);
                $this->secretService->createByData($secretData);
            }

            // desireList round1
            foreach ($event->getFirstRound()->getParticipants() as $participant) {
                $this->storeDesireListForUserAndEvent($event, $event->getFirstRound(), $participant);
            }

            // desireList round2
            foreach ($event->getSecondRound()->getParticipants() as $participant) {
                $desireLists = $this->desireListService->findByUserAndEvent($participant, $event->getFirstRound());
                if (count($desireLists) > 0) {
                    $desireList = $desireLists[0];

                    $desireListData = (new DesireListData())->initFromEntity($desireList);
                    $desireListData->setEvents(array_merge($desireListData->getEvents(), [$event->getSecondRound()]));
                    $this->desireListService->update($desireList, $desireListData);

                } else {
                    $this->storeDesireListForUserAndEvent($event, $event->getSecondRound(), $participant);
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
        return $this->pickSecretByEventAndUser($event, $event->getFirstRound(), $participant, SecretSantaState::PHASE_2);
    }

    public function performSecondRoundPick(SecretSantaEvent $event, User $participant): Secret
    {
        return $this->pickSecretByEventAndUser($event, $event->getSecondRound(), $participant, SecretSantaState::RUNNING);
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
            throw new \Exception('Secret for event and user is not unique!');
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

    public function storeDesireListForUserAndEvent(SecretSantaEvent $secretEvent, Event $event, User $participant): DesireList
    {
        $desireListData = new DesireListData();
        $name = $secretEvent->getName();
        $desireListData->setName($name);
        $desireListData->setDescription('This list was autogenerated for event: ' . $name);
        $desireListData->setOwner($participant);
        $desireListData->setEvents([$event]);
        $desireListData->setDesires([]);

        return $this->desireListService->createByData($desireListData);
    }
}
