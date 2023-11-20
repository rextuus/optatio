<?php

namespace App\Twig\Components;

use App\Entity\Desire;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent()]
final class DesireComponent
{
    use DefaultActionTrait;

    public Desire $desire;
}
