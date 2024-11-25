<?php
declare(strict_types=1);

namespace App\Tests;

use App\Content\SecretSanta\Calculation\SecretCalculator;
use App\Entity\Exclusion;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

/**
 * @author Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2023 DocCheck Community GmbH
 */
class SecretCalculatorTest extends TestCase
{
    private SecretCalculator $calculator;

    public function setUp(): void
    {
        parent::setUp();

        $this->calculator = new SecretCalculator();
    }

    public function testCalculateSecretsWithoutShips(): void
    {
        /*
         * R1: 1 => 2, 2 => 3, 3 => 1 || 1 => 3, 2 => 1, 3 => 2
         * R2: 1 => 2, 2 => 3, 4 => 1 || 1 => 4, 2 => 1, 4 => 2
         */
        $user1 = $this->getUser(1, 'User1');
        $user2 = $this->getUser(2, 'User2');
        $user3 = $this->getUser(3, 'User3');
        $user4 = $this->getUser(4, 'User4');

        // easiest example: we use same combination in both rounds => only possible solution are 3 pairs of users presenting each other
        $userRound1 = [
            $user1,
            $user2,
            $user3,
        ];
        $userRound2 = [
            $user1,
            $user2,
            $user3,
        ];

        $result = $this->calculator->testCalculateSecretsForDoubleRound($userRound1, $userRound2);
        $this->assertTrue($result->isSuccess());
        $this->assertTrue($result->checkIntegrity());
    }

    public function testCalculateSecretsWithShipsShouldFailDueToImpossibleRounds(): void
    {
        /*
         * R1: 1 => 2, 2 => 3, 3 => 1 || 1 => 3, 2 => 1, 3 => 2
         * R2: 1 => 2, 2 => 3, 4 => 1 || 1 => 4, 2 => 1, 4 => 2
         */
        $user1 = $this->getUser(1, 'User1');
        $user2 = $this->getUser(2, 'User2');
        $user3 = $this->getUser(3, 'User3');
        $user4 = $this->getUser(4, 'User4');

        $userRound1 = [
            $user1,
            $user2,
            $user3,
        ];
        $userRound2 = [
            $user1,
            $user2,
            $user3,
        ];

        // this exclusion will make it impossible to find a unique combination for both rounds
        $exclusion = new Exclusion();
        $exclusion->setExclusionCreator($user1);
        $exclusion->setExcludedUser($user2);

        $result = $this->calculator->testCalculateSecretsForDoubleRound($userRound1, $userRound2, [$exclusion]);
        $this->assertFalse($result->isSuccess());
        $this->assertTrue($result->checkIntegrity());
    }

    public function testCalculateSecretsWithShipsShouldFindAValidCombination(): void
    {
        /*
         * R1: 1 => 2, 2 => 3, 3 => 1 || 1 => 3, 2 => 1, 3 => 2
         * R2: 1 => 2, 2 => 3, 4 => 1 || 1 => 4, 2 => 1, 4 => 2
         */
        $user1 = $this->getUser(1, 'User1');
        $user2 = $this->getUser(2, 'User2');
        $user3 = $this->getUser(3, 'User3');
        $user4 = $this->getUser(4, 'User4');

        $userRound1 = [
            $user1,
            $user2,
            $user3,
        ];
        $userRound2 = [
            $user1,
            $user2,
            $user4,
        ];

        // if we exclude only in one direction we can solve it
        $exclusion = new Exclusion();
        $exclusion->setExclusionCreator($user1);
        $exclusion->setExcludedUser($user3);

        $result = $this->calculator->testCalculateSecretsForDoubleRound($userRound1, $userRound2, [$exclusion]);
        $this->assertTrue($result->isSuccess());
        $this->assertTrue($result->checkIntegrity());
    }

    public function testCalculateSecretsWithCorrespondingShipsShouldFindAValidCombination(): void
    {
        /*
         * R1: 1 => 2, 2 => 3, 3 => 1 || 1 => 3, 2 => 1, 3 => 2
         * R2: 1 => 2, 2 => 3, 4 => 1 || 1 => 4, 2 => 1, 4 => 2
         */
        $user1 = $this->getUser(1, 'User1');
        $user2 = $this->getUser(2, 'User2');
        $user3 = $this->getUser(3, 'User3');
        $user4 = $this->getUser(4, 'User4');

        $userRound1 = [
            $user1,
            $user2,
            $user3,
        ];
        $userRound2 = [
            $user1,
            $user2,
            $user4,
        ];

        // if we exclude in both directions there is no solution
        $exclusion = new Exclusion();
        $exclusion->setExclusionCreator($user1);
        $exclusion->setExcludedUser($user3);
        $exclusion->setBidirectional(true);

        $result = $this->calculator->testCalculateSecretsForDoubleRound($userRound1, $userRound2, [$exclusion]);
        $this->assertFalse($result->isSuccess());
        $this->assertTrue($result->checkIntegrity());
    }

    private function getUser(int $id, string $name): User
    {
        $user = new User();
        $user->setId($id);
        $user->setFirstName($name);

        return $user;
    }
}
