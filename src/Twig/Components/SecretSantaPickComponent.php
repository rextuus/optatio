<?php

namespace App\Twig\Components;

use App\Entity\Secret;
use App\Entity\SecretSantaEvent;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent()]
final class SecretSantaPickComponent
{
    use DefaultActionTrait;

    public Secret $secret;
}
