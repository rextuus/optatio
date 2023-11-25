<?php

namespace App\Twig\Components;

use App\Content\Desire\DesireState;
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
            return 'Freigeben/Bestätigen';
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
                return $reservation->getOwner()->getId() === $user->getId();
            }
        )) > 0;
    }

    public function checkIsReservedGeneral(): bool
    {
        return $this->desire->getReservations()->count() > 0;
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

    public function getHeaderClass(): string
    {
        if ($this->checkIsReservedGeneral()){
            return 'reserved';
        }
        return '';
    }
}
