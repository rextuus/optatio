<?php

namespace App\Twig\Components;

use App\Content\Desire\DesireState;
use App\Content\Reservation\ReservationState;
use App\Entity\Desire;
use App\Entity\DesireList;
use App\Entity\Reservation;
use App\Entity\User;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent()]
final class ForeignDesireComponent
{
    use DefaultActionTrait;

    public User $currentUser;
    public Desire $desire;
    public DesireList $desireList;
    public bool $isReservedByCurrenUser;

    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {

    }

    public function isActive(): string
    {
        if ($this->desire->isListed()) {
            return 'active';
        }
        return '';
    }

    public function isExclusive(): string
    {
        if ($this->desire->isExclusive()) {
            return 'active';
        }
        return '';
    }

    public function isExactly(): string
    {
        if ($this->desire->isExactly()) {
            return 'active';
        }
        return '';
    }

    public function getReserveButtonText(): string
    {
        if ($this->checkIsReservedByUser()){
            return 'Freigeben';
        }
        if ($this->checkIsReservedGeneral()){
            return 'Reservieren';
        }
        return 'Reservieren';
    }

    public function getHeaderText(): string
    {
        if ($this->checkIsReservedByUser()){
            return 'Von dir reserviert';
        }
        if ($this->checkIsResolvedByUser()){
            return 'Von dir besorgt ('.$this->desire->getId().')';
        }
        if ($this->checkIsReservedGeneral()){
            return 'Von jemand anderem reserviert';
        }

        return 'Verfügbar';
    }

    public function getReserveDisabled(): string
    {
        if ($this->checkIsReservedByUser()){
            return '';
        }

        if ($this->checkIsReservedGeneral() && !$this->desire->isExclusive()) {
            return '';
        }
        if ($this->checkIsReservedGeneral()) {
            return 'disabled';
        }
        return '';
    }

    public function checkIsReservedByUser(): bool
    {
        $user = $this->currentUser;
        return count($this->desire->getReservations()->filter(
            function (Reservation $reservation) use ($user) {
                return $reservation->getOwner()->getId() === $user->getId() && $reservation->getState() === ReservationState::RESERVED;
            }
        )) > 0;
    }

    public function checkIsResolvedByUser(): bool
    {
        $user = $this->currentUser;
        return count($this->desire->getReservations()->filter(
                function (Reservation $reservation) use ($user) {
                    return $reservation->getOwner()->getId() === $user->getId() && $reservation->getState() === ReservationState::RESOLVED;
                }
            )) > 0;
    }

    public function checkIsReservedGeneral(): bool
    {
        return $this->desire->getReservations()->count() > 0;
    }
    #[Route('/confirm/{desireList}/{desire}', name: 'app_desire_resolve')]
    public function confirmDesire(DesireList $desireList, Desire $desire): Response
    {
        $user = $this->getLoggedInUser();
        $check = $this->checkDesireListAccess($user, $desireList);
        if ($check) {
            return $check;
        }

        $this->desireManager->resolveReservation($user, $desire);

        return $this->render('desire/list_foreign.html.twig', [
            'desireList' => $desireList,
            'desire' => $desire,
        ]);
    }
    public function getButtonLink(): string
    {

        if ($this->checkIsReservedByUser()) {
            return $this->urlGenerator->generate(
                'app_desire_release',
                [
                    'desireList' => $this->desireList->getId(),
                    'desire' => $this->desire->getId(),
                ]
            );
        }

        return $this->urlGenerator->generate(
            'app_desire_reserve',
            [
                'desireList' => $this->desireList->getId(),
                'desire' => $this->desire->getId(),
            ]
        );
    }

    public function getResolveButtonLink(): string
    {
        return $this->urlGenerator->generate(
            'app_desire_resolve',
            [
                'desireList' => $this->desireList->getId(),
                'desire' => $this->desire->getId(),
            ]
        );
    }

    public function getHeaderClass(): string
    {
        if ($this->checkIsReservedGeneral()){
            return 'reserved';
        }
        return '';
    }
}
