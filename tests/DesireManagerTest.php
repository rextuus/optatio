<?php
declare(strict_types=1);

namespace App\Test;

use App\Content\Desire\DesireManager;
use App\Content\Desire\DesireState;
use App\Content\Reservation\ReservationState;
use App\Entity\Desire;
use App\Entity\Reservation;
use App\Entity\User;

class DesireManagerTest extends IntegrationTestCase
{
    private DesireManager $desireManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loadFixtureFiles(
            [
                __DIR__ . '/fixtures/desires.yml',
            ]
        );
        $this->desireManager = $this->getService(DesireManager::class);
    }

    //RESERVING
    public function testReservationProcessForNonListedDesire(): void
    {
        /** @var User $user5 */
        $user5 = $this->getFixtureEntityByIdent('user5');

        /** @var Desire $desire */
        $desire = $this->getFixtureEntityByIdent('hidden_desire_of_user_1_free');

        $this->expectExceptionMessage('Cant make a reservation for a non listed desire');

        $reservation = $this->desireManager->addReservation($user5, $desire);
    }

    public function testReservationProcessForExclusiveDesire(): void
    {
        /** @var User $user5 */
        $user5 = $this->getFixtureEntityByIdent('user5');

        /** @var User $user4 */
        $user4 = $this->getFixtureEntityByIdent('user4');

        /** @var Desire $desire */
        $desire = $this->getFixtureEntityByIdent('desire_of_user_1_free');

        $reservation = $this->desireManager->addReservation($user5, $desire);

        $this->assertEquals($user5, $reservation->getOwner());
        $this->assertEquals($desire, $reservation->getDesire());

        // check desire has new state
        $this->assertEquals(DesireState::RESERVED, $desire->getState());

        // try to reserve with another user
        $this->expectExceptionMessage('Cant make a second reservation for a non exclusive desire');
        $reservation = $this->desireManager->addReservation($user4, $desire);
    }

    public function testReservationProcessForNonExclusiveDesire(): void
    {
        /** @var User $user5 */
        $user5 = $this->getFixtureEntityByIdent('user5');

        /** @var User $user4 */
        $user4 = $this->getFixtureEntityByIdent('user4');

        /** @var Desire $desire */
        $desire = $this->getFixtureEntityByIdent('exclusive_desire_of_user_1_free');

        $reservation = $this->desireManager->addReservation($user5, $desire);

        $this->assertEquals($user5, $reservation->getOwner());
        $this->assertEquals($desire, $reservation->getDesire());

        // check desire has new state
        $this->assertEquals(DesireState::RESERVED, $desire->getState());

        // try to reserve with another user
        $reservation = $this->desireManager->addReservation($user4, $desire);

        $this->assertEquals($user4, $reservation->getOwner());
        $this->assertEquals($desire, $reservation->getDesire());

        // check desire has new state
        $this->assertEquals(DesireState::MULTIPLE_RESERVED, $desire->getState());
    }

    // REMOVAL
    public function testRemoveReservationFromMultiDesireUntilOnlyResolvedAreRemaining(): void
    {
        /** @var User $user2 */
        $user2 = $this->getFixtureEntityByIdent('user2');

        /** @var User $user3 */
        $user3 = $this->getFixtureEntityByIdent('user3');

        /** @var User $user4 */
        $user4 = $this->getFixtureEntityByIdent('user4');

        /** @var Desire $desire */
        $desire = $this->getFixtureEntityByIdent('multiple_reserved_desire_of_user_1_free');
        $reservationsAtStart = 4;

        $this->assertEquals(DesireState::MULTIPLE_RESERVED_OR_RESOLVED, $desire->getState());
        $this->assertCount($reservationsAtStart, $desire->getReservations()->toArray());

        // remove both reservations with state reserved
        $this->desireManager->removeReservation($user2, $desire);

        $this->assertEquals(DesireState::MULTIPLE_RESERVED_OR_RESOLVED, $desire->getState());
        $this->assertCount($reservationsAtStart-1, $desire->getReservations()->toArray());

        $this->desireManager->removeReservation($user3, $desire);

        $this->assertEquals(DesireState::MULTIPLE_RESOLVED, $desire->getState());
        $this->assertCount($reservationsAtStart-2, $desire->getReservations()->toArray());

        // try to remove one an already resolved reservation
        $this->expectExceptionMessage('Cant remove an already resolved reservation');
        $this->desireManager->removeReservation($user4, $desire);
    }


    public function testRemoveReservationFromMultiDesireUntilItsFreeAgain(): void
    {
        /** @var User $user2 */
        $user2 = $this->getFixtureEntityByIdent('user2');

        /** @var User $user3 */
        $user3 = $this->getFixtureEntityByIdent('user3');

        /** @var Desire $desire */
        $desire = $this->getFixtureEntityByIdent('multiple_reserved_desire_of_user_1_free2');
        $reservationsAtStart = 2;

        $this->assertEquals(DesireState::MULTIPLE_RESERVED, $desire->getState());
        $this->assertCount($reservationsAtStart, $desire->getReservations()->toArray());

        // remove both reservations with state reserved
        $this->desireManager->removeReservation($user2, $desire);

        $this->assertEquals(DesireState::RESERVED, $desire->getState());
        $this->assertCount($reservationsAtStart-1, $desire->getReservations()->toArray());

        $this->desireManager->removeReservation($user3, $desire);

        $this->assertEquals(DesireState::FREE, $desire->getState());
        $this->assertCount($reservationsAtStart-2, $desire->getReservations()->toArray());
    }

    // RESOLVING
    public function testResolveReservationFromMultiDesireUntilItsFreeAgain(): void
    {
        /** @var User $user2 */
        $user2 = $this->getFixtureEntityByIdent('user2');

        /** @var User $user3 */
        $user3 = $this->getFixtureEntityByIdent('user3');

        /** @var Reservation $reservation1 */
        $reservation1 = $this->getFixtureEntityByIdent('open_reservation_for_multiple_reserved_desire2');

        /** @var Reservation $reservation2 */
        $reservation2 = $this->getFixtureEntityByIdent('open_reservation2_for_multiple_reserved_desire2');


        /** @var Desire $desire */
        $desire = $this->getFixtureEntityByIdent('multiple_reserved_desire_of_user_1_free2');
        $reservationsAtStart = 2;

        $this->assertEquals(DesireState::MULTIPLE_RESERVED, $desire->getState());
        $this->assertCount($reservationsAtStart, $desire->getReservations()->toArray());

        // resolve both reservations with state reserved
        $this->desireManager->resolveReservation($user2, $desire);
        $this->assertEquals(ReservationState::RESOLVED, $reservation1->getState());

        $this->assertEquals(DesireState::MULTIPLE_RESERVED_OR_RESOLVED, $desire->getState());

        $this->desireManager->resolveReservation($user3, $desire);

        $this->assertEquals(DesireState::MULTIPLE_RESOLVED, $desire->getState());
        $this->assertEquals(ReservationState::RESOLVED, $reservation2->getState());
    }
}