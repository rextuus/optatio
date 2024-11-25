<?php
declare(strict_types=1);

namespace App\Content\SecretSanta\Calculation;

use App\Entity\Exclusion;
use App\Entity\SecretSantaEvent;
use App\Entity\User;

/**
 * @author Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2023 DocCheck Community GmbH
 */
class SecretCalculator
{

    public function testCalculateSecrets(SecretSantaEvent $event): CalculationResult
    {
        $participantsFirstRound = $event->getFirstRound()->getParticipantsWithoutGodFathers($event);

        if ($event->isIsDoubleRound()){
            $participantsSecondRound = $event->getSecondRound()->getParticipantsWithoutGodFathers($event);

            return $this->testCalculateSecretsForDoubleRound(
                $participantsFirstRound,
                $participantsSecondRound,
                $event->getExclusions()->toArray()
            );
        }
        return $this->testCalculateSecretsForSingleRound(
            $participantsFirstRound,
            $event->getExclusions()->toArray()
        );
    }

    /**
     * @param User[] $userRound1
     * @param User[] $userRound2
     * @param Exclusion[] $exclusions
     */
    public function testCalculateSecretsForDoubleRound(array $userRound1, array $userRound2, array $exclusions = []): CalculationResult
    {
        $tries = 0;
        $success = false;
        $secretsRound1 = [];
        $secretsRound2 = [];
        while (!$success && $tries < 20){
            $userIds = array_map(
                function (User $user){
                    return $user->getId();
                },
                $userRound1
            );

            $secretsRound1 = $this->calculateUserSecretCombination($userIds, $exclusions);

            $userIds = array_map(
                function (User $user){
                    return $user->getId();
                },
                $userRound2
            );

            $secretsRound2 = $this->calculateUserSecretCombination($userIds, $exclusions, $secretsRound1);

            $success = count($secretsRound1) > 0 &&  count($secretsRound2) > 0;
            $tries++;
        }

        return new CalculationResult($secretsRound1, $secretsRound2, true);
    }

    /**
     * @param User[] $userRound1
     * @param Exclusion[] $exclusions
     */
    public function testCalculateSecretsForSingleRound(array $userRound1, array $exclusions = []): CalculationResult
    {
        $tries = 0;
        $success = false;
        $secretsRound1 = [];
        $secretsRound2 = [];
        while (!$success && $tries < 20){
            $userIds = array_map(
                function (User $user){
                    return $user->getId();
                },
                $userRound1
            );

            $secretsRound1 = $this->calculateUserSecretCombination($userIds, $exclusions);

            $success = count($secretsRound1) > 0;
            $tries++;
        }

        return new CalculationResult($secretsRound1, $secretsRound2);
    }

    /**
     * @param int[] $userIds
     * @param Exclusion[] $exclusions
     * @param PotentialSecret[] $secretsRound1
     * @return PotentialSecret[]
     */
    private function calculateUserSecretCombination(array $userIds, array $exclusions = [], array $secretsRound1 = []): array
    {
        $secrets = [];
        $tries = 0;
        while (count($secrets) === 0 && $tries < 10) {
            $provider = [];
            $receiver = [];
            for ($secretNr = 0; $secretNr < count($userIds); $secretNr++) {
                $randomProvider = $this->getRandomProvider($userIds, $provider);

                // forbidden receivers are: itself + receiver from round1 + ships
                $forbidden = [$randomProvider];
                if (count($secretsRound1) > 0){
                    foreach ($secretsRound1 as $secret){
                        if ($secret->getProvider() === $randomProvider){
                            $forbidden[] = $secret->getReceiver();
                        }
                    }
                }
                if (count($exclusions) > 0){
                    foreach ($exclusions as $exclusion){
                        if ($exclusion->getForbiddenUserId($randomProvider)){
                            $forbidden[] = $exclusion->getForbiddenUserId($randomProvider);
                        }
                    }
                }

                $randomReceiver = $this->getRandomReceiver($userIds, $receiver, $forbidden);
                $secrets[] = new PotentialSecret($randomProvider, $randomReceiver);
            }

            /** @var PotentialSecret[] $secrets */
            foreach ($secrets as $secret) {
                if ($secret->isFaulty()) {
                    $secrets = [];
                }
            }
            $tries++;
        }

        return $secrets;
    }

    private function getRandomProvider(array $userIds, &$provider): ?int
    {
        $tries = 0;
        while ($tries < 10) {
            $randomIndex = rand(0, count($userIds) - 1);
            $randomUser = $userIds[$randomIndex];
            if (!array_key_exists($randomUser, $provider)) {
                $provider[$randomUser] = 1;
                return $randomUser;
            }
            $tries++;
        }
        return null;
    }

    private function getRandomReceiver(array $userIds, &$receiver, array $forbiddenUsers = []): ?int
    {
        $tries = 0;
        while ($tries < 10) {
            $randomIndex = rand(0, count($userIds) - 1);
            $randomUser = $userIds[$randomIndex];
            if (!array_key_exists($randomUser, $receiver) && !in_array($randomUser, $forbiddenUsers)) {
                $receiver[$randomUser] = 1;
                return $randomUser;
            }
            $tries++;
        }
        return null;
    }
}
