<?php

namespace App\Twig\Components;

use App\Entity\Desire;
use App\Entity\DesireList;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent()]
final class DesireComponent
{
    use DefaultActionTrait;

    public Desire $desire;
    public DesireList $desireList;
    public bool $disableUp;
    public bool $disableDown;

    public function isActive(): string
    {
        if ($this->desire->isListed()){
            return 'active';
        }
        return '';
    }

    public function isExclusive(): string
    {
        if ($this->desire->isExclusive()){
            return 'active';
        }
        return '';
    }

    public function isExactly(): string
    {
        if ($this->desire->isExactly()){
            return 'active';
        }
        return '';
    }

    public function getDisableUp(): string
    {
        if ($this->disableUp){
            return 'disabled';
        }
        return '';
    }

    public function getDisableDown(): string
    {
        if ($this->disableDown){
            return 'disabled';
        }
        return '';
    }
}
